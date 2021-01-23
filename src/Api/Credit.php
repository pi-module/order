<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Laminas\Json\Json;

/*
 * Pi::api('credit', 'order')->getCredit($uid);
 * Pi::api('credit', 'order')->getCreditList($uidList);
 * Pi::api('credit', 'order')->addHistory($history, $order, $invoice, $status);
 * Pi::api('credit', 'order')->acceptOrderCredit($order, $invoice = 0);
 * Pi::api('credit', 'order')->addCredit($uid, $amount, $fluctuation, $action, $messageAdmin, $messageUser);
 * Pi::api('credit', 'order')->canonizeCredit($credit);
 */

class Credit extends AbstractApi
{
    public function getCredit($uid = '')
    {
        // Get user id if not set
        if (empty($uid)) {
            $uid = Pi::user()->getId();
        }
        // Check user id
        if (!$uid || $uid == 0) {
            return [];
        }
        // Get credit
        $credit = Pi::model('credit', $this->getModule())->find($uid, 'uid');
        return $this->canonizeCredit($credit);
    }

    public function getCreditList($uidList)
    {
        $creditList = [];
        $where      = ['uid' => $uidList];

        $select = Pi::model('credit', $this->getModule())->select()->where($where);
        $rowset = Pi::model('credit', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $creditList[$row->uid] = $this->canonizeCredit($row);
        }

        foreach ($uidList as $uid) {
            if (!isset($creditList[$uid])) {
                $creditList[$uid] = $this->canonizeCredit([]);
            }
        }

        return $creditList;
    }

    public function addHistory($history, $order = 0, $invoice = 0, $status = 0)
    {
        $row                     = Pi::model('credit_history', $this->getModule())->createRow();
        $row->uid                = isset($history['uid']) ? $history['uid'] : Pi::user()->getId();
        $row->time_create        = time();
        $row->order              = $order;
        $row->invoice            = $invoice;
        $row->amount             = $history['amount'];
        $row->amount_old         = isset($history['amount_old']) ? $history['amount_old'] : '';
        $row->amount_new         = isset($history['amount_new']) ? $history['amount_new'] : '';
        $row->status             = $status;
        $row->status_fluctuation = $history['status_fluctuation'];
        $row->status_action      = $history['status_action'];
        $row->message_user       = $history['message_user'];
        $row->message_admin      = $history['message_admin'];
        $row->ip                 = Pi::user()->getIp();
        $row->module             = $history['module'];
        $row->save();
    }

    public function acceptOrderCredit($order, $invoice = 0)
    {
        // Update history
        $where   = ['order' => $order];
        $select  = Pi::model('credit_history', $this->getModule())->select()->where($where)->limit(1);
        $history = Pi::model('credit_history', $this->getModule())->selectWith($select)->current();
        if (!empty($history)) {
            // Check
            if ($history->status == 0) {
                // Find credit
                $credit = Pi::model('credit_history', $this->getModule())->find($history->uid, 'uid');
                if ($credit) {
                    switch ($history->status_fluctuation) {
                        case 'increase':
                            $credit->amount = $credit->amount + $history->amount;
                            break;

                        case 'decrease':
                            $credit->amount = $credit->amount - $history->amount;
                            break;
                    }
                    // Set amount detail
                    $detail                   = json::decode($credit->amount_detail, true);
                    $detail[$history->module] = $credit->amount;
                    $credit->amount_detail    = Json::encode($detail);
                    // Save
                    $credit->time_update = time();
                    $credit->save();
                } else {
                    // Set detail
                    $detail = [
                        $history->module => $history->amount,
                    ];
                    // Save credit
                    $credit                = Pi::model('credit_history', $this->getModule())->createRow();
                    $credit->uid           = $history->uid;
                    $credit->time_update   = time();
                    $credit->amount        = $history->amount;
                    $credit->amount_detail = Json::encode($detail);
                    $credit->save();
                }
                $history->invoice = $invoice;
                $history->status  = 1;
                $history->save();
            }
        }
    }

    public function addCredit($uid, $amount, $fluctuation = 'increase', $action = 'manual', $messageAdmin = '', $messageUser = '', $module = 'order')
    {
        // Set result
        $result = [
            'status'  => 0,
            'message' => '',
        ];
        // Find and set credit
        $credit = Pi::model('credit', $this->getModule())->find($uid, 'uid');
        if ($credit) {
            // Set old credit amount
            $amountOld = $credit->amount;
            // Do action
            $detail = json::decode($credit->amount_detail, true);
            switch ($fluctuation) {
                case 'increase':
                    $credit->amount  = $credit->amount + $amount;
                    $detail[$module] = $amount;
                    break;

                case 'decrease':
                    if ($credit->amount >= $amount) {
                        $credit->amount  = $credit->amount - $amount;
                        $detail[$module] = $amount;
                    } else {
                        $result['message'] = __('Your input amount is more than user credit');
                        return $result;
                    }
                    break;
            }
            $credit->amount_detail = Json::encode($detail);
            $credit->time_update   = time();
            $credit->save();
            // Set new credit amount
            $amountNew = $credit->amount;
        } else {
            if ($fluctuation == 'increase') {
                // Set detail
                $detail = [
                    $module => $amount,
                ];
                // Save credit
                $credit                = Pi::model('credit', $this->getModule())->createRow();
                $credit->uid           = $uid;
                $credit->time_update   = time();
                $credit->amount        = $amount;
                $credit->amount_detail = Json::encode($detail);
                $credit->save();
                // Set old credit amount
                $amountOld = 0;
                // Set new credit amount
                $amountNew = $credit->amount;
            } else {
                $result['message'] = __('This user never use credit system, than you can not decrease amnout from him / her');
                return $result;
            }
        }
        // Add history
        $history = [
            'uid'                => $uid,
            'amount'             => $amount,
            'amount_old'         => $amountOld,
            'amount_new'         => $amountNew,
            'status_fluctuation' => $fluctuation,
            'status_action'      => $action,
            'message_user'       => $messageAdmin,
            'message_admin'      => $messageUser,
            'module'             => $module,
        ];
        $this->addHistory($history, 0, 0, 1);
        // Return result
        $result['status'] = 1;
        return $result;
    }

    public function canonizeCredit($credit)
    {
        if ($credit) {
            $credit                     = $credit->toArray();
            $credit['amount_view']      = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view'] = ($credit['time_update'] > 0) ? _date($credit['time_update']) : __('Never update');
            if (!empty($credit['amount_detail'])) {
                $moduleList                   = Pi::registry('modulelist')->read();
                $amountDetail                 = json::decode($credit['amount_detail'], true);
                $credit['amount_detail_view'] = [];
                foreach ($amountDetail as $module => $amount) {
                    $credit['amount_detail_view'][$module]                 = [];
                    $credit['amount_detail_view'][$module]['module_name']  = $module;
                    $credit['amount_detail_view'][$module]['module_title'] = $moduleList[$module]['title'];
                    $credit['amount_detail_view'][$module]['amount']       = $amount;
                    $credit['amount_detail_view'][$module]['amount_view']  = Pi::api('api', 'order')->viewPrice($amount);
                }
            }
        } else {
            $credit                       = [];
            $credit['amount']             = 0;
            $credit['amount_view']        = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view']   = __('Never update');
            $credit['amount_detail']      = [];
            $credit['amount_detail_view'] = [];
        }

        return $credit;
    }
}

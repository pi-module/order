<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Json\Json;

/*
 * Pi::api('credit', 'order')->getCredit($uid);
 * Pi::api('credit', 'order')->addHistory($history, $order, $invoice, $status);
 * Pi::api('credit', 'order')->acceptOrderCredit($order, $invoice = 0);
 * Pi::api('credit', 'order')->addCredit($uid, $amount, $fluctuation, $action, $messageAdmin, $messageUser);
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
            return array();
        }
        // Get credit
        $credit = Pi::model('credit', $this->getModule())->find($uid, 'uid');
        if ($credit) {
            $credit = $credit->toArray();
            $credit['amount_view'] = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view'] = ($credit['time_update'] > 0) ? _date($credit['time_update']) : __('Never update');
            if (!empty($credit['amount_detail'])) {
                $moduleList = Pi::registry('modulelist')->read();
                $amountDetail = json::decode($credit['amount_detail'], true);
                $credit['amount_detail_view'] = array();
                foreach ($amountDetail as $module => $amount) {
                    $credit['amount_detail_view'][$module] = array();
                    $credit['amount_detail_view'][$module]['module_name'] = $module;
                    $credit['amount_detail_view'][$module]['module_title'] = $moduleList[$module]['title'];
                    $credit['amount_detail_view'][$module]['amount'] = $amount;
                    $credit['amount_detail_view'][$module]['amount_view'] = Pi::api('api', 'order')->viewPrice($amount);
                }
            }
        } else {
            $credit = array();
            $credit['amount'] = 0;
            $credit['amount_view'] = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view'] = __('Never update');
            $credit['amount_detail'] = array();
            $credit['amount_detail_view'] = array();
        }
        return $credit;
    }

    public function addHistory($history, $order = 0, $invoice = 0, $status = 0)
    {
        $row = Pi::model('history', $this->getModule())->createRow();
        $row->uid = isset($history['uid']) ? $history['uid'] : Pi::user()->getId();
        $row->time_create = time();
        $row->order = $order;
        $row->invoice = $invoice;
        $row->amount = $history['amount'];
        $row->amount_old = isset($history['amount_old']) ? $history['amount_old'] : '';
        $row->status = $status;
        $row->status_fluctuation = $history['status_fluctuation'];
        $row->status_action = $history['status_action'];
        $row->message_user = $history['message_user'];
        $row->message_admin = $history['message_admin'];
        $row->ip = Pi::user()->getIp();
        $row->module = $history['module'];
        $row->save();
    }

    public function acceptOrderCredit($order, $invoice = 0)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Update history
        $where = array('order' => $order);
        $select = Pi::model('history', $this->getModule())->select()->where($where)->limit(1);
        $history = Pi::model('history', $this->getModule())->selectWith($select)->current();
        if (!empty($history)) {
            // Check
            if ($history->status == 0) {
                // Find credit
                $credit = Pi::model('credit', $this->getModule())->find($history->uid, 'uid');
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
                    $detail = json::decode($credit->amount_detail, true);
                    $detail[$history->module] = $credit->amount;
                    $credit->amount_detail = Json::encode($detail);
                    // Save
                    $credit->time_update = time();
                    $credit->save();
                } else {
                    // Set detail
                    $detail = array(
                        $history->module => $history->amount,
                    );
                    // Save credit
                    $credit = Pi::model('credit', $this->getModule())->createRow();
                    $credit->uid = $history->uid;
                    $credit->time_update = time();
                    $credit->amount = $history->amount;
                    $credit->amount_detail = Json::encode($detail);
                    $credit->save();
                }
                $history->invoice = $invoice;
                $history->status = 1;
                $history->save();
            }
        }
    }

    public function addCredit($uid, $amount, $fluctuation = 'increase', $action = 'manual', $messageAdmin = '', $messageUser = '', $module = 'shop')
    {
        // Set result
        $result = array(
            'status' => 0,
            'message' => '',
        );
        $amountOld = 0;
        // Find and set credit
        $credit = Pi::model('credit', $this->getModule())->find($uid, 'uid');
        if ($credit) {
            $amountOld = $credit->amount;
            $detail = json::decode($credit->amount_detail, true);
            switch ($fluctuation) {
                case 'increase':
                    $credit->amount = $credit->amount + $amount;
                    $detail[$module] = $amount;
                    break;

                case 'decrease':
                    if ($credit->amount >= $amount) {
                        $credit->amount = $credit->amount - $amount;
                        $detail[$module] = $amount;
                    } else {
                        $result['message'] = __('Your input amount is more than user credit');
                        return $result;
                    }
                    break;
            }
            $credit->amount_detail = Json::encode($detail);
            $credit->time_update = time();
            $credit->save();
        } else {
            if ($fluctuation == 'increase') {
                // Set detail
                $detail = array(
                    $module => $amount,
                );
                // Save credit
                $credit = Pi::model('credit', $this->getModule())->createRow();
                $credit->uid = $uid;
                $credit->time_update = time();
                $credit->amount = $amount;
                $credit->amount_detail = Json::encode($detail);
                $credit->save();
            } else {
                $result['message'] = __('This user never use credit system, than you can not decrease amnout from him / her');
                return $result;
            }
        }
        // Add history
        $history = array(
            'amount' => $amount,
            'amount_old' => $amountOld,
            'status_fluctuation' => $fluctuation,
            'status_action' => $action,
            'message_user' => $messageAdmin,
            'message_admin' => $messageUser,
            'module' => $module,
        );
        $this->addHistory($history, 0, 0, 1);
        // Return result
        $result['status'] = 1;
        return $result;
    }
}
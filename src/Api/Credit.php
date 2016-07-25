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

/*
 * Pi::api('credit', 'order')->addHistory($history, $order, $invoice, $status);
 * Pi::api('credit', 'order')->acceptOrderCredit($order, $invoice = 0);
 */

class Credit extends AbstractApi
{
    public function addHistory($history, $order = 0 , $invoice = 0, $status = 0)
    {
        $row = Pi::model('history', $this->getModule())->createRow();
        $row->uid = $history['uid'];
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
        $row->save();
    }

    public function acceptOrderCredit($order, $invoice = 0)
    {
        // Update history
        $where = array('order' => $order);
        $select = Pi::model('history', $this->getModule())->select()->where($where);
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
                    $credit->save();
                } else {
                    $credit = Pi::model('credit', $this->getModule())->createRow();
                    $credit->uid = $history->uid;
                    $credit->amount = $history->amount;
                    $credit->save();
                }
                $history->invoice = $invoice;
                $history->status = 1;
                $history->save();
            }
        }
    }
}
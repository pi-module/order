<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */

namespace Module\Order\Api;

use Module\Order\Gateway\AbstractGateway;
use Pi;
use Pi\Application\Api\AbstractApi;


class Stripe extends AbstractApi
{
    public function getOrderByTransfertIds($transfertIds)
    {

        if (empty($transfertIds) || count($transfertIds) == 0) {
            return [];
        }

        foreach ($transfertIds as $transfert) {
            $whereId[]  = 'invoice_installment.extra LIKE "%\"transfer\":\"' . $transfert . '\"%"';
        }
        $where = implode(' OR ', $whereId);

        $orderTable              = Pi::model('order', 'order')->getTable();
        $invoiceTable            = Pi::model("invoice", 'order')->getTable();
        $invoiceInstallmentTable = Pi::model("invoice_installment", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])
            ->join(['invoice' => $invoiceTable], 'invoice.order = order.id', [])
            ->join(['invoice_installment' => $invoiceInstallmentTable], 'invoice_installment.invoice = invoice.id', [])
            ->where($where);

        $rowset = Pi::db()->query($select);

        foreach ($rowset as $row) {
            $list[$row['id']] = $row;
        }
        return $list;
    }

    public function getOrderByPaymentIntentIds($paymentIntentIds)
    {
        if (empty($paymentIntentIds) || count($paymentIntentIds) == 0) {
            return [];
        }

        foreach ($paymentIntentIds as $paymentIntent) {
            $whereId[]  = 'extra LIKE "%\"payment_intent\":\"' . $paymentIntent . '\"%"';
        }
        $where = implode(' OR ', $whereId);

        $select = Pi::model('order', 'order')->select()->where($where);
        $rowset = Pi::model('order', 'order')->selectWith($select);
        $list = [];
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
        }
        return $list;
    }

}	
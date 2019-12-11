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
            $whereId[]  = 'extra LIKE "%\"transfer\":\"' . $transfert . '\"%"';
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
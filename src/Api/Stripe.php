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

/*
 * Pi::api('stripe', 'order')->getOrderByTransfertIds($transfertIds);
 * Pi::api('stripe', 'order')->getOrderByPaymentIntentIds($paymentIntentIds);
 * Pi::api('stripe', 'order')->getStripeResponse();
 * Pi::api('stripe', 'order')->preparingSubscription($params);
 */

class Stripe extends AbstractApi
{
    public function getOrderByTransfertIds($transfertIds)
    {

        if (empty($transfertIds) || count($transfertIds) == 0) {
            return [];
        }

        foreach ($transfertIds as $transfert) {
            $whereId[] = 'invoice_installment.extra LIKE "%\"transfer\":\"' . $transfert . '\"%"';
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
            $whereId[] = 'extra LIKE "%\"payment_intent\":\"' . $paymentIntent . '\"%"';
        }
        $where = implode(' OR ', $whereId);

        $select = Pi::model('order', 'order')->select()->where($where);
        $rowset = Pi::model('order', 'order')->selectWith($select);
        $list   = [];
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
        }
        return $list;
    }

    public function getStripeResponse()
    {
        $body       = @file_get_contents('php://input');
        $event_json = json_decode($body);
        return $event_json;
    }

    public function preparingSubscription($params)
    {
        $uid = Pi::user()->getId();

        // Check and create customer
        $customer = Pi::model('subscription_customer', $this->getModule())->find($uid, 'uid');
        if (!$customer) {
            // create customer on stripe
            $customerStripe = \Stripe\Customer::create(
                [
                    'source' => $params['source'],
                    'email'  => $params['email'],
                    'name'   => $params['name'],
                    'phone'  => $params['phone'],
                ]
            );

            // Save customer to database
            $customer = Pi::model('subscription_customer', $this->getModule())->createRow();
            $customer->assign(
                [
                    'uid'      => $uid,
                    'customer' => $customerStripe,
                ]
            );
            $customer->save();
        }
        $customer = $customer->toArray();

        // Check and create product
        $where  = ['module' => $params['module'], 'product_id' => $params['product_id']];
        $select = Pi::model('subscription_productn', $this->getModule())->select()->where($where);
        $rowset = Pi::model('subscription_product', $this->getModule())->selectWith($select);
        if (!$rowset) {
            // create product on stripe
            $productStripe = \Stripe\Product::create(
                [
                    'name'        => $params['product_name'], // ToDo : set true field
                    'type'        => $params['product_type'], // ToDo : set true field
                    'description' => $params['product_description'],
                ]
            );

            // create product price on stripe
            $priceStripe = \Stripe\Price::create(
                [
                    'product'     => $productStripe->id,
                    'unit_amount' => $params['amount'],
                    'currency'    => $params['currency'],
                    'recurring'   => [
                        'interval' => $params['interval'],
                    ],
                ]
            );

            // Save product to database
            $product = Pi::model('subscription_product', $this->getModule())->createRow();
            $product->assign(
                [
                    'module'            => $params['module'], // ToDo : set true field
                    'product_type'      => $params['product_type'], // ToDo : set true field
                    'product_id'        => $params['product_id'], // ToDo : set true field
                    'amount'            => $params['amount'],
                    'interval'          => $params['interval'],
                    'stripe_product_id' => $productStripe->id,
                    'stripe_price_id'   => $priceStripe->id,
                ]
            );
            $product->save();

        } else {
            $product = $rowset->current();
        }
        $product = $product->toArray();


        // Set


        $where  = ['order' => $params['order'], 'uid' => $uid];
        $select = Pi::model('subscription_detail', $this->getModule())->select()->where($where);
        $rowset = Pi::model('subscription_detail', $this->getModule())->selectWith($select);
        if ($rowset) {
            // ToDo : if subscribed before
        }

        // create subscription on stripe
        $subscriptionStripe = \Stripe\Subscriptions::create(
            [
                'customer' => $customer['customer'],
                'items'    => [
                    [
                        'price' => $product['stripe_price_id'],
                    ],
                ],
            ]
        );


        // Save plan to database
        $detail = Pi::model('subscription_detail', $this->getModule())->createRow();
        $detail->assign(
            [
                'uid'             => $uid,
                'order'           => $params['order'],
                'subscription_id' => $subscriptionStripe->id,
                'time_create'     => time(),
                'extra'           => json_encode($subscriptionStripe),
            ]
        );
        $detail->save();


        return $detail->toArray();
    }

    public function updateInvoiceStatus($params)
    {
    }

    public function updateInvoice($params)
    {
    }

    public function createProduct($params)
    {
    }

    public function createPlan($params)
    {
    }

    public function createSubscription($params)
    {
    }

    public function retrieveSubscription($params)
    {
    }

    public function updateSubscription($params)
    {
    }

    public function cancelSubscription($params)
    {
    }

    public function listSubscription($params)
    {
    }
}
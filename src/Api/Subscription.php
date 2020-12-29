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

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('subscription', 'order')->getApiKeys();
 * Pi::api('subscription', 'order')->getStripeResponse();
 * Pi::api('subscription', 'order')->preparingCustomer();
 * Pi::api('subscription', 'order')->preparingProduct($params);
 * Pi::api('subscription', 'order')->preparingSubscription($params, $customer, $product);
 * Pi::api('subscription', 'order')->updateInvoiceStatus($params);
 * Pi::api('subscription', 'order')->updateInvoice($params);
 * Pi::api('subscription', 'order')->createProduct($params);
 * Pi::api('subscription', 'order')->createPlan($params);
 * Pi::api('subscription', 'order')->createSubscription($params);
 * Pi::api('subscription', 'order')->retrieveSubscription($params);
 * Pi::api('subscription', 'order')->updateSubscription($params);
 * Pi::api('subscription', 'order')->cancelSubscription($params);
 * Pi::api('subscription', 'order')->listSubscription($params);
 */

class Subscription extends AbstractApi
{
    public function getApiKeys()
    {
        return '123abc';
    }

    public function getStripeResponse()
    {
        $body       = @file_get_contents('php://input');
        $event_json = json_decode($body);
        return $event_json;
    }

    public function preparingCustomer()
    {
        // ToDo : add PaymentMethod

        // Get user
        $user = Pi::api('user', 'order')->getUserInformation();

        // Check and create customer
        $customer = Pi::model('subscription_customer', $this->getModule())->find($user['id'], 'uid');

        // Check
        if (!$customer) {

            // create customer on stripe
            $customerStripe = \Stripe\Customer::create(
                [
                    'email' => $user['email'],
                    'name'  => $user['name'],
                    'phone' => $user['mobile'],
                ]
            );

            // Save customer to database
            $customer = Pi::model('subscription_customer', $this->getModule())->createRow();
            $customer->assign(
                [
                    'uid'      => $user['id'],
                    'customer' => $customerStripe,
                ]
            );
            $customer->save();
        }

        return $customer->toArray();
    }

    public function preparingProduct($params)
    {
        // Set where
        $where = [
            'service_module' => $params['service_module'],
            'service_id'     => $params['service_id'],
        ];

        // Select
        $select = Pi::model('subscription_product', $this->getModule())->select()->where($where);
        $rowset = Pi::model('subscription_product', $this->getModule())->selectWith($select);

        // Check
        if (!$rowset) {
            // create product on stripe
            $productStripe = \Stripe\Product::create(
                [
                    'name' => $params['service_name'],
                    'type' => 'service',
                ]
            );

            // create product price on stripe
            $priceStripe = \Stripe\Price::create(
                [
                    'product'     => $productStripe->id,
                    'unit_amount' => $params['service_amount'],
                    'currency'    => Pi::config('number_currency'),
                    'recurring'   => [
                        'interval' => $params['service_interval'],
                    ],
                ]
            );

            // Save product to database
            $product = Pi::model('subscription_product', $this->getModule())->createRow();
            $product->assign(
                [
                    'service_module'    => $params['service_module'],
                    'service_id'        => $params['service_id'],
                    'service_amount'    => $params['service_amount'],
                    'service_interval'  => $params['service_interval'],
                    'stripe_product_id' => $productStripe->id,
                    'stripe_price_id'   => $priceStripe->id,
                ]
            );
            $product->save();
        } else {
            $product = $rowset->current();
        }

        return $product->toArray();
    }

    public function preparingSubscription($params, $customer, $product)
    {
        // Set default result
        $result = [
            'result' => false,
            'data'   => [],
            'error'  => [
                'code'    => 1,
                'message' => __('Nothing selected'),
            ],
        ];

        // Get user id
        $uid = Pi::user()->getId();

        // Where
        $where = [
            'subscription_product' => $product['stripe_product_id'],
            'uid'                  => $uid,
        ];

        // Select
        $select = Pi::model('subscription_detail', $this->getModule())->select()->where($where);
        $rowset = Pi::model('subscription_detail', $this->getModule())->selectWith($select);

        // Check
        if ($rowset) {

            // Set message
            $result['error'] = [
                'code'    => 2,
                'message' => __('You subscript to this server before'),
            ];

            return $result;
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

        // Save subscription to database
        $detail = Pi::model('subscription_detail', $this->getModule())->createRow();
        $detail->assign(
            [
                'uid'                   => $uid,
                'subscription_id'       => $subscriptionStripe->id,
                'subscription_product'  => $product['stripe_product_id'],
                'subscription_interval' => $product['service_interval'],
                //'subscription_status', // ToDo : add it
                'subscription_customer' => $customer['customer'],
                //'subscription_create_time', // ToDo : add it
                //'current_period_start', // ToDo : add it
                //'current_period_end', // ToDo : add it
                'time_create'           => time(),
                'extra'                 => json_encode($subscriptionStripe),
            ]
        );
        $detail->save();

        // Set result
        $result = [
            'result' => true,
            'data'   => $detail->toArray(),
            'error'  => [],
        ];

        return $result;
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

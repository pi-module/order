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

namespace Module\Order\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class SubscriptionController extends IndexController
{
    // Ajax method
    public function createAction()
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

        // Check this method just call by login users
        if (!Pi::service('user')->hasIdentity()) {
            // Set message
            $result['error'] = [
                'code'    => 2,
                'message' => __('You need login to system for subscription'),
            ];

            return $result;
        }

        // Get info from url
        $params = [
            'service_id'       => $this->params('service_id'),
            'service_title'    => $this->params('service_title'),
            'service_module'   => $this->params('service_module'),
            'service_amount'   => $this->params('service_amount'),
            'service_interval' => $this->params('service_interval'),
        ];

        // Check
        foreach ($params as $key => $value) {
            if (empty($value)) {

                // Set message
                $result['error'] = [
                    'code'    => 3,
                    'message' => sprintf(__('%s not set'), $key),
                ];

                return $result;
            }
        }

        // preparing customer
        $customer = Pi::api('subscription', 'order')->preparingCustomer();

        // preparing product
        $product = Pi::api('subscription', 'order')->preparingProduct($params);

        // preparing subscription
        $subscription = Pi::api('subscription', 'order')->preparingSubscription($params, $customer, $product);

        // Check result
        if (!$subscription['result']) {

            // Set message
            $result['error'] = [
                'code'    => $subscription['error']['code'],
                'message' => $subscription['error']['message'],
            ];

            return $result;
        }

        // Set default result
        $result = [
            'result' => true,
            'data'   => $subscription['data'],
            'error'  => [],
        ];

        return $result;
    }

    // Ajax method
    public function cancelAction()
    {
    }

    // Stripe webhook
    public function responseAction()
    {
        // ToDo : this method still not secure !!!!
        $response = Pi::api('subscription', 'order')->getStripeResponse();

        // Check response not empty
        if (!empty($response)) {
            switch ($response->type) {
                case "invoice.payment_succeeded":
                    $params                   = [];
                    $params["id"]             = $response->data->object->id;
                    $params["invoice_status"] = $response->data->object->status;

                    Pi::api('subscription', 'order')->updateInvoiceStatus($params);

                    break;

                case "invoice.payment_failed":
                    $params                   = [];
                    $params["id"]             = $response->data->object->id;
                    $params["invoice_status"] = $response->data->object->status;

                    Pi::api('subscription', 'order')->updateInvoiceStatus($params);

                    break;

                case "customer.subscription.created":
                    $params                              = [];
                    $params["id"]                        = $response->data->object->id;
                    $params["customer_id"]               = $response->data->object->customer;
                    $params["subscription_product"]      = $response->data->object->plan->id;
                    $params["subscription_interval"]     = $response->data->object->plan->interval_count . " " . $response->data->object->plan->interval;
                    $params["subscription_status"]       = $response->data->object->status;
                    $params["current_period_start"]      = date("Y-m-d H:i:s", $response->data->object->current_period_start);
                    $params["current_period_end"]        = date("Y-m-d H:i:s", $response->data->object->current_period_end);
                    $params["subscription_created_date"] = date("Y-m-d H:i:s", $response->data->object->created);

                    Pi::api('subscription', 'order')->createSubscription($params);

                    break;

                case "customer.subscription.updated":
                    $params                        = [];
                    $params["id"]                  = $response->data->object->id;
                    $params["subscription_status"] = $response->data->object->status;

                    Pi::api('subscription', 'order')->updateSubscription($params);

                    break;

                case "invoice.finalized":
                    $params                           = [];
                    $params["id"]                     = $response->data->object->id;
                    $params["invoice_finalized_date"] = date("Y-m-d H:i:s", $response->data->object->finalized_at);
                    $params["invoice_status"]         = $response->data->object->status;

                    Pi::api('subscription', 'order')->updateInvoice($params);

                    break;

                // ToDo : need it ?
                /*
                case "customer.created":
                    $params                   = [];
                    $params["customer_id"]    = $response->data->object->id;
                    $params["customer_email"] = $response->data->object->email;
                    // ToDo : insertCustomer($params);
                    break;
                */

                // ToDo : need it ?
                /* case "invoice.created":
                    $params                         = [];
                    $params["id"]                   = $response->data->object->id;
                    $params["subscription_id"]      = $response->data->object->subscription;
                    $params["invoice_number"]       = $response->data->object->number;
                    $params["customer_id"]          = $response->data->object->customer;
                    $params["billing_email"]        = $response->data->object->customer_email;
                    $params["currency"]             = $response->data->object->currency;
                    $params["invoice_status"]       = $response->data->object->status;
                    $params["invoice_created_date"] = date("Y-m-d H:i:s", $response->data->object->created);

                    $i = 0;
                    foreach ($response->data->object->lines->data as $data) {
                        $params["invoice_items"][$i]["amount"]      = $data->amount;
                        $params["invoice_items"][$i]["currency"]    = $data->currency;
                        $params["invoice_items"][$i]["quantity"]    = $data->quantity;
                        $params["invoice_items"][$i]["description"] = $data->description;
                        $i++;
                    }

                    // ToDo : insertInvoice($params);
                    break; */
            }
        }
    }
}

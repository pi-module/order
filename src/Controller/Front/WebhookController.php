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

class WebhookController extends IndexController
{
    public function responseAction()
    {
        // ToDo : this method still not secure !!!!
        $response = Pi::api('stripe', 'order')->getStripeResponse();

        // Check response not empty
        if (!empty($response)) {
            
            switch ($response->type) {
                case "invoice.payment_succeeded":
                    $params                   = [];
                    $params["id"]             = $response->data->object->id;
                    $params["invoice_status"] = $response->data->object->status;

                    Pi::api('stripe', 'order')->updateInvoiceStatus($params);
         
                    break;

                case "invoice.payment_failed":
                    $params                   = [];
                    $params["id"]             = $response->data->object->id;
                    $params["invoice_status"] = $response->data->object->status;

                    Pi::api('stripe', 'order')->updateInvoiceStatus($params);

                    break;

                case "customer.subscription.created":
                    $params                              = [];
                    $params["id"]                        = $response->data->object->id;
                    $params["customer_id"]               = $response->data->object->customer;
                    $params["subscription_plan"]         = $response->data->object->plan->id;
                    $params["subscription_interval"]     = $response->data->object->plan->interval_count . " " . $response->data->object->plan->interval;
                    $params["subscription_status"]       = $response->data->object->status;
                    $params["current_period_start"]      = date("Y-m-d H:i:s", $response->data->object->current_period_start);
                    $params["current_period_end"]        = date("Y-m-d H:i:s", $response->data->object->current_period_end);
                    $params["subscription_created_date"] = date("Y-m-d H:i:s", $response->data->object->created);

                    Pi::api('stripe', 'order')->createSubscription($params);

                    break;

                case "customer.subscription.updated":
                    $params                        = [];
                    $params["id"]                  = $response->data->object->id;
                    $params["subscription_status"] = $response->data->object->status;

                    Pi::api('stripe', 'order')->updateSubscription($params);

                    break;

                case "invoice.finalized":
                    $params                   = [];
                    $params["id"]                     = $response->data->object->id;
                    $params["invoice_finalized_date"] = date("Y-m-d H:i:s", $response->data->object->finalized_at);
                    $params["invoice_status"]         = $response->data->object->status;

                    Pi::api('stripe', 'order')->updateInvoice($params);

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
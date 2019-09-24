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

namespace Module\Order\Gateway\Stripe;

use Module\Order\Gateway\AbstractGateway;
use Pi;
use Zend\Json\Json;

class Gateway extends AbstractGateway
{
    public function __construct()
    {
        parent::__construct();
        $this->_type      = AbstractGateway::TYPE_STRIPE;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getAuthority()
    {
        // Get config
        $config = Pi::service('registry')->config->read('order');
        // Get order
        $order = Pi::api('order', 'order')->getOrder($this->gatewayOrder['id']);
        // Get product list
        $products = Pi::api('order', 'order')->listProduct($order['id']);
        // Set products to payment
        $i = 1;
        foreach ($products as $product) {
            $this->gatewayPayInformation['item_name_' . $i]      = str_replace('<br>', ' : ',  $product['details']['title']);
            $this->gatewayPayInformation['item_number_' . $i]    = $product['number'];
            $this->gatewayPayInformation['quantity_' . $i]       = 1;
            $this->gatewayPayInformation['amount_' . $i]         = $product['product_price'];
            $this->gatewayPayInformation['tax_' . $i]            = $product['vat_price'];
            $this->gatewayPayInformation['discount_price_' . $i] = $product['discount_price'];
            $this->gatewayPayInformation['unconsumed_' . $i]     = $product['extra']['unconsumedPrice'];
            $i++;
        }
        // Set address
        $address        = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $addressCompose = '';
        if ($config['order_address1'] && $config['order_address2']) {
            if (!empty($address['address1'])) {
                $addressCompose = $address['address1'];
            } elseif (!empty($address['address2'])) {
                $addressCompose = $address['address2'];
            }
        } elseif ($config['order_address1']) {
            $addressCompose = $address['address1'];
        } elseif ($config['order_address2']) {
            $addressCompose = $address['address2'];
        }
        // Set payment information
        $this->gatewayPayInformation['nb_product']       = $i;
        $this->gatewayPayInformation['no_shipping']      = count($products);
        $this->gatewayPayInformation['first_name']       = $address['first_name'];
        $this->gatewayPayInformation['last_name']        = $address['last_name'];
        $this->gatewayPayInformation['address1']         = $addressCompose;
        $this->gatewayPayInformation['address2']         = $address['address2'];
        $this->gatewayPayInformation['address_override'] = 1;
        $this->gatewayPayInformation['city']             = $address['city'];
        $this->gatewayPayInformation['state']            = $address['state'];
        $this->gatewayPayInformation['country']          = $address['country'];
        $this->gatewayPayInformation['zip']              = $address['zip_code'];
        $this->gatewayPayInformation['email']            = $address['email'];
        $this->gatewayPayInformation['night_phone_b']    = $address['mobile'];
        $this->gatewayPayInformation['cmd']              = '_cart';
        $this->gatewayPayInformation['upload']           = 1;
        $this->gatewayPayInformation['return']           = $this->gatewayFinishUrl;
        $this->gatewayPayInformation['cancel_return']    = $this->gatewayCancelUrl;
        $this->gatewayPayInformation['notify_url']       = $this->gatewayNotifyUrl;
        $this->gatewayPayInformation['invoice']          = $this->gatewayInvoice['random_id'];
        $this->gatewayPayInformation['business']         = $this->gatewayOption['business'];
        $this->gatewayPayInformation['currency_code']    = $this->gatewayOption['currency'];
        $this->gatewayPayInformation['image_url']        = $config['payment_image'];
        // Set for test mode
        if ($this->gatewayOption['test_mode']) {
            $this->gatewayPayInformation['username']  = $this->gatewayOption['username'];
            $this->gatewayPayInformation['password']  = $this->gatewayOption['password'];
            $this->gatewayPayInformation['signature'] = $this->gatewayOption['signature'];
        }
    }

    public function getSession($order)
    {
        \Stripe\Stripe::setApiKey($this->gatewayOption['password']);

        $items    = [];
        $subtotal = 0;
        $tax      = 0;
        for ($i = 1; $i < $this->gatewayPayInformation['nb_product']; ++$i) {
            $item                = [];
            $item["name"]        = addcslashes($this->gatewayPayInformation['item_name_' . $i], '"');
            $item["amount"]       = ($this->gatewayPayInformation['amount_' . $i] + $this->gatewayPayInformation['tax_' . $i]) * 100;
            $item["currency"]    = $this->gatewayPayInformation['currency_code'];
            $item["quantity"]    = $this->gatewayPayInformation['quantity_' . $i];
            $item["description"] = addcslashes($this->gatewayPayInformation['item_name_' . $i], '"');
            $items[]             = $item;


            $subtotal += $this->gatewayPayInformation['amount_' . $i] - $this->gatewayPayInformation['discount_price_' . $i]
                - $this->gatewayPayInformation['unconsumed_' . $i];
            $tax      += $this->gatewayPayInformation['tax_' . $i];
        }


/*
            if ($this->gatewayPayInformation['discount_price_' . $i] > 0) {
                $item             = [];
                $item["name"]     = __('Discount');
                $item["price"]    = -$this->gatewayPayInformation['discount_price_' . $i];
                $item["currency"] = $this->gatewayPayInformation['currency_code'];
                $item["quantity"] = 1;
                $item["tax"]      = 0;
                $items[]          = $item;
            }

            if ($this->gatewayPayInformation['unconsumed_' . $i] > 0) {

                $item             = [];
                $item["name"]     = __('Old package recovery');
                $item["price"]    = -$this->gatewayPayInformation['unconsumed_' . $i];
                $item["currency"] = $this->gatewayPayInformation['currency_code'];
                $item["quantity"] = 1;
                $item["tax"]      = 0;
                $items[]          = $item;
            }

*/


        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $items,
            'success_url' => $this->gatewayPayInformation['return'] . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->gatewayPayInformation['cancel_return'],
            'client_reference_id' => 'order-' . $order['id'] . '-uid-' . $order['uid'],
            //'mode' => 'setup'
        ]);

        return $session;

    }


    public function setAdapter()
    {
        $this->gatewayAdapter = 'Stripe';
    }

    public function setInformation()
    {
        $gateway                  = [];
        $gateway['title']         = __('Stripe');
        $gateway['path']          = 'Stripe';
        $gateway['type']          = 'online';
        $gateway['version']       = '1.0';
        $gateway['description']   = '';
        $gateway['author']        = 'Mickael STAMM <contact@sta2m.com>';
        $gateway['credits']       = '@sta2m';
        $gateway['releaseDate']   = 1567692940;
        $this->gatewayInformation = $gateway;
        return $gateway;
    }

    public function setSettingForm()
    {
        $form = [];
        // form path
        $form['path'] = [
            'name'     => 'path',
            'label'    => __('path'),
            'type'     => 'hidden',
            'required' => true,
        ];

        $form['onemail'] = [
            'name'     => 'onemail',
            'label'    => __('Send only 1 mail after payment (only for end user, not for admin)'),
            'type'     => 'checkbox',
            'required' => false,
        ];

        // business
        $form['business'] = [
            'name'     => 'business',
            'label'    => __('Stripe email address'),
            'type'     => 'text',
            'required' => true,
        ];

        $form['invoice_name'] = [
            'name'     => 'invoice_name',
            'label'    => __('Gateway Name on the Invoice'),
            'type'     => 'text',
            'required' => false,
        ];
        // currency
        $form['currency'] = [
            'name'     => 'currency',
            'label'    => __('Stripe currency'),
            'type'     => 'text',
            'required' => true,
        ];

        // Username
        $form['username'] = [
            'name'     => 'username',
            'label'    => __('Public key'),
            'type'     => 'text',
            'required' => false,
        ];
        // password
        $form['password'] = [
            'name'     => 'password',
            'label'    => __('Secret key'),
            'type'     => 'text',
            'required' => false,
        ];

        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        return;
    }

    public function setRedirectUrl()
    {
        $this->getAuthority();

    }

    public function verifyPayment($request, $processing)
    {

        \Stripe\Stripe::setApiKey($this->gatewayOption['password']);

        $session = \Stripe\Checkout\Session::Retrieve($request['stripe_session_id']);
        $pi = $session['payment_intent'];
        $payment = \Stripe\PaymentIntent::retrieve($pi);


        $order     = Pi::api('order', 'order')->getOrder($processing['order']);
        $extra     = json_decode($order['extra']);

        if ($payment['status'] == 'succeeded') {
            $result['status']  = 1;
            $result['adapter'] = $this->gatewayAdapter;
            $result['order']   = $order['id'];
        } else {
            $result['status']   = 0;
            $result['state']    = $payment['status'] ;
            $this->gatewayError = __('An error occured with Stripe. Please contact administrator');
        }

        return $result;


        /*
                $setup = \Stripe\SetupIntent::retrieve($session['setup_intent']);
                $ayment = \Stripe\PaymentIntent::create([
                    'amount' => 2000,
                    'currency' => 'eur',
                    'payment_method_types' => ['card'],
                ]);*/
        return false;
        // Need
    }

    public function setMessage($log)
    {
        $message = '';
        return $message;
    }

    public function setPaymentError($id = '')
    {
        // Set error
        $this->gatewayError = '';
    }


    public function getDescription()
    {
        return __(
            'To install the Stripe Driver : <br>
Get yours credentials on stripe. <br>
You can use the developper mode with developpers keys proveded by stripe for your test and also check the error mode (no payment), to fine tune your code<br>
        '
        );
    }
}

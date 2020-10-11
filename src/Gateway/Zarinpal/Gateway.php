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

namespace Module\Order\Gateway\Zarinpal;

use Module\Order\Gateway\AbstractGateway;
use Pi;
use Laminas\Json\Json;
use Laminas\Soap\Client as LaminasSoapClient;

class Gateway extends AbstractGateway
{
    public function __construct()
    {
        parent::__construct();
        $this->_type = AbstractGateway::TYPE_FORM;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setAdapter()
    {
        $this->gatewayAdapter = 'Zarinpal';
    }

    public function setInformation()
    {
        $gateway = [
            'title'       => __('Zarinpal'),
            'path'        => 'Zarinpal',
            'type'        => 'online',
            'version'     => '1.0',
            'description' => '',
            'author'      => 'Hossein Azizabadi <azizabadi@faragostaresh.com>',
            'credits'     => '@voltan',
            'releaseDate' => 1380802565,
        ];

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

        // form pin
        $form['id'] = [
            'name'     => 'MerchantID',
            'label'    => __('MerchantID'),
            'type'     => 'text',
            'required' => true,
        ];

        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        $form = [];

        // form RefId
        $form['result'] = [
            'name' => 'result',
            'type' => 'hidden',
        ];

        $this->gatewayPayForm = $form;
        return $this;
    }

    public function setRedirectUrl()
    {
        // Get order
        $order = Pi::api('order', 'order')->getOrder($this->gatewayOrder['id']);

        // Get product list
        $products = Pi::api('order', 'order')->listProduct($order['id']);

        // Get address list
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');

        // Set total price
        $total = 0;
        foreach ($products as $product) {
            $total = $total + $product['vat_price'];
        }

        // Set parameters
        $parameters = [
            'MerchantID'  => $this->gatewayOption['MerchantID'],
            'Description' => sprintf('order id : %s', $this->gatewayOrder['random_id']),
            'Amount'      => intval($total) / 10,
            'Email'       => $address['email'],
            'Mobile'      => $address['mobile'],
            'CallbackURL' => $this->gatewayBackUrl,
            'payerId'     => 0,
        ];

        // Call
        $client = new LaminasSoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $call   = $client->PaymentRequest($parameters);

        // Check result
        if ($call->Status == 100) {
            $this->gatewayPayInformation = [
                'Status'    => $call->Status,
                'Authority' => $call->Authority,
            ];
            $this->gatewayRedirectUrl    = sprintf('https://www.zarinpal.com/pg/StartPay/%s', $call->Authority);
        } else {
            $this->setPaymentError(0);

            // set log
            $log              = [];
            $log['gateway']   = $this->gatewayAdapter;
            $log['authority'] = $call->Authority;
            $log['value']     = Json::encode([$this->gatewayInvoice, (array)$call]);
            $log['invoice']   = $this->gatewayInvoice['id'];
            $log['amount']    = intval($this->gatewayInvoice['total_price']);
            $log['status']    = 0;
            $log['message']   = 'ERR: ' . $call->Status;
            Pi::api('log', 'order')->setLog($log);
        }
    }

    public function verifyPayment($request, $processing)
    {
        // Set result
        $result = [
            'status' => 0,
        ];

        // Get order
        $order = Pi::api('order', 'order')->getOrder($processing['order']);

        // Get product list
        $products = Pi::api('order', 'order')->listProduct($order['id']);

        $total = 0;
        foreach ($products as $product) {
            $total = $total + $product['vat_price'];
        }

        // Check Status
        if ($request['Status'] == 'OK') {

            // Set parameters
            $parameters = [
                'MerchantID' => $this->gatewayOption['MerchantID'],
                'Authority'  => $request['Authority'],
                'Amount'     => intval($total) / 10,

            ];

            // Call
            $client = new LaminasSoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
            $call   = $client->PaymentVerification($parameters);

            // Check
            if ($call->Status == 100) {
                $result['status'] = 1;

                // Get invoice
                $message = __('Your payment were successfully.');

                // Set log value
                $value                        = [];
                $value['request']             = $request;
                $value['PaymentVerification'] = (array)$call;
                $value                        = Json::encode($value);

                // Set log
                $log              = [];
                $log['gateway']   = $this->gatewayAdapter;
                $log['authority'] = $request['authority'];
                $log['value']     = $value;
                $log['order']     = $order['id'];
                $log['amount']    = $total;
                $log['status']    = 1;
                $log['message']   = $message;
                $logResult        = Pi::api('log', 'order')->setLog($log);
                // Update invoice
                //if ($logResult) {
                //    $invoice          = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
                //    $result['status'] = 1;
                //}
            } else {
                // Set log value
                $value            = [];
                $value['request'] = $request;
                $value            = Json::encode($value);
                // Set log
                $log              = [];
                $log['gateway']   = $this->gatewayAdapter;
                $log['authority'] = $request['authority'];
                $log['value']     = $value;
                $log['invoice']   = $order['id'];
                $log['amount']    = $total;
                $log['status']    = 0;
                $log['message']   = sprintf(__('Transation failed. Status: %s'), $result['Status']);
                Pi::api('log', 'order')->setLog($log);
            }
        } else {
            // Set log value
            $value            = [];
            $value['request'] = $request;
            $value            = Json::encode($value);
            // Set log
            $log              = [];
            $log['gateway']   = $this->gatewayAdapter;
            $log['authority'] = $request['authority'];
            $log['value']     = $value;
            $log['invoice']   = $order['id'];
            $log['amount']    = $total;
            $log['status']    = 0;
            $log['message']   = __('Transaction canceled by user');
            Pi::api('log', 'order')->setLog($log);
        }

        // Set result
        $result['adapter'] = $this->gatewayAdapter;
        $result['invoice'] = $invoice['id'];
        $result['order']   = $order['id'];
        return $result;
    }

    public function setMessage($log)
    {
        $message = '';
        return $message;
    }

    public function setPaymentError($id = '')
    {
        $error = '';
        // Set error
        $this->gatewayError = $error;
        return $error;
    }
}

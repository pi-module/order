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
        // $order = Pi::api('order', 'order')->getOrder($this->gatewayOrder['id']);

        // Get product list
        $products = Pi::api('order', 'order')->listProduct($this->gatewayOrder['id']);

        // Get address list
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($this->gatewayOrder['id'], 'INVOICING');

        // Set total price
        $total = 0;
        $totalDiscount = 0;
        foreach ($products as $product) {
            $total = $total + $product['product_price'];
            $totalDiscount = $totalDiscount + $product['discount_price'];
        }
        $total = $total - $totalDiscount;

        // Set order Id for payment
        $orderId = $this->gatewayOrder['id'];
        if (count($this->gatewayOrder['installments']) == 1) {
            $installment = array_shift($this->gatewayOrder['installments']);
            $orderId     = Pi::api('invoice', 'order')->getIdForPayment($installment['invoice']);
        }

        // Set parameters
        $parameters = [
            'MerchantID'  => $this->gatewayOption['MerchantID'],
            'Description' => sprintf('order id : %s', $orderId),
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
            $log['amount']    = intval($total);
            $log['status']    = 0;
            $log['message']   = 'ERR: ' . $call->Status;
            Pi::api('log', 'order')->setLog($log);
        }
    }

    public function verifyPayment($request, $processing)
    {
        // Set result
        $result = [
            'status'  => 0,
            'adapter' => $this->gatewayAdapter,
            'order'   => $processing['order'],
        ];

        // Get order
        $order = Pi::api('order', 'order')->getOrder($processing['order']);

        // Get product list
        $products = Pi::api('order', 'order')->listProduct($order['id']);

        // Set total price
        $total = 0;
        $totalDiscount = 0;
        foreach ($products as $product) {
            $total = $total + $product['product_price'];
            $totalDiscount = $totalDiscount + $product['discount_price'];
        }
        $total = $total - $totalDiscount;

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
                $logStatus        = 1;
                $message          = __('Your payment were successfully.');
            } else {
                $message = sprintf(__('Transation failed. Status: %s'), $result['Status']);
            }
        } else {
            $message = __('Transaction canceled by user');
        }

        // Set log
        $log = [
            'gateway'   => $this->gatewayAdapter,
            'authority' => $request['authority'],
            'order'     => $order['id'],
            'amount'    => intval($total),
            'status'    => isset($logStatus) ? $logStatus : 0,
            'message'   => isset($message) ? $message : '',
            'value'     => json_encode(
                [
                    'request'         => $request,
                    'bpVerifyRequest' => isset($call) ? (array)$call : '',

                ]
            ),
        ];

        // Save log
        Pi::api('log', 'order')->setLog($log);

        // Return result
        return $result;
    }

    public function setMessage($log)
    {
        return '';
    }

    public function setPaymentError($id = '')
    {
        $error = '';

        // Set error
        $this->gatewayError = $error;
        return $error;
    }
}

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
use Zend\Json\Json;
use Zend\Soap\Client as ZendSoapClient;

class Gateway extends AbstractGateway
{
    public function setAdapter()
    {
        $this->gatewayAdapter = 'Zarinpal';
    }

    public function setInformation()
    {
        $gateway                  = [];
        $gateway['title']         = __('Zarinpal');
        $gateway['path']          = 'Zarinpal';
        $gateway['type']          = 'online';
        $gateway['version']       = '1.0';
        $gateway['description']   = '';
        $gateway['author']        = 'Hossein Azizabadi <azizabadi@faragostaresh.com>';
        $gateway['credits']       = '@voltan';
        $gateway['releaseDate']   = 1380802565;
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
        $form['id']               = [
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
        $form['result']       = [
            'name' => 'result',
            'type' => 'hidden',
        ];
        $this->gatewayPayForm = $form;
        return $this;
    }

    public function setRedirectUrl()
    {
        // Get order
        $order = Pi::api('order', 'order')->getOrder($this->gatewayInvoice['order']);
        // Set parameters
        $parameters                = [];
        $parameters['MerchantID']  = $this->gatewayOption['MerchantID'];
        $parameters['Description'] = sprintf('order id : %s', $this->gatewayInvoice['random_id']);
        $parameters['Amount']      = intval($this->gatewayInvoice['total_price']) / 10;
        $parameters['Email']       = $order['email'];
        $parameters['Mobile']      = $order['mobile'];
        $parameters['CallbackURL'] = $this->gatewayBackUrl;
        $parameters['payerId']     = 0;
        // Call
        $client = new ZendSoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $call   = $client->PaymentRequest($parameters);
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
        $result           = [];
        $result['status'] = 0;
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($processing['random_id'], 'random_id');
        // Check Status
        if ($request['Status'] == 'OK') {
            // Set parameters
            $parameters               = [];
            $parameters['MerchantID'] = $this->gatewayOption['MerchantID'];
            $parameters['Authority']  = $request['Authority'];
            $parameters['Amount']     = intval($invoice['total_price']) / 10;
            // Call
            $client = new ZendSoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
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
                $log['invoice']   = $invoice['id'];
                $log['amount']    = $invoice['total_price'];
                $log['status']    = 1;
                $log['message']   = $message;
                $logResult        = Pi::api('log', 'order')->setLog($log);
                // Update invoice
                if ($logResult) {
                    $invoice          = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
                    $result['status'] = 1;
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
                $log['invoice']   = $invoice['id'];
                $log['amount']    = $invoice['total_price'];
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
            $log['invoice']   = $invoice['id'];
            $log['amount']    = $invoice['total_price'];
            $log['status']    = 0;
            $log['message']   = __('Transaction canceled by user');
            Pi::api('log', 'order')->setLog($log);
        }
        // Set result
        $result['adapter'] = $this->gatewayAdapter;
        $result['invoice'] = $invoice['id'];
        $result['order']   = $invoice['order'];
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
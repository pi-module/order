<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Gateway\Zarinpal;

use Pi;
use Module\Order\Gateway\AbstractGateway;
use Zend\Json\Json;

class Gateway extends AbstractGateway
{
    public function setAdapter()
    {
        $this->gatewayAdapter = 'Zarinpal';
    }

    public function setInformation()
    {
        $gateway = array();
        $gateway['title'] = __('Zarinpal');
        $gateway['path'] = 'Zarinpal';
        $gateway['type'] = 'online';
        $gateway['version'] = '1.0';
        $gateway['description'] = '';
        $gateway['author'] = 'Hossein Azizabadi <azizabadi@faragostaresh.com>';
        $gateway['credits'] = '@voltan';
        $gateway['releaseDate'] = 1380802565;
        $this->gatewayInformation = $gateway;
        return $gateway;
    }

    public function setSettingForm()
    {
        $form = array();
        // form path
        $form['path'] = array(
            'name' => 'path',
            'label' => __('path'),
            'type' => 'hidden',
            'required' => true,
        );
        // form pin
        $form['id'] = array(
            'name' => 'MerchantID',
            'label' => __('MerchantID'),
            'type' => 'text',
            'required' => true,
        );
        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        $form = array();
        // form RefId
        $form['result'] = array(
            'name' => 'result',
            'type' => 'hidden',
        );
        $this->gatewayPayForm = $form;
        return $this;
    }

    public function getDialogUrl()
    {
        return 'https://www.zarinpal.com/pg/services/WebGate/wsdl';
    }

    public function setRedirectUrl()
    {
        // Get order
        $order = Pi::api('order', 'order')->getOrder($this->gatewayInvoice['order']);
        // Set parameters
        $parameters = array();
        $parameters['MerchantID'] = $this->gatewayOption['MerchantID'];
        $parameters['Description'] = sprintf('order id : %s', $this->gatewayInvoice['random_id']);
        $parameters['Amount'] = intval($this->gatewayInvoice['total_price']);
        $parameters['Email'] = $order['email'];
        $parameters['Mobile'] = $order['mobile'];
        $parameters['CallbackURL'] = $this->gatewayBackUrl;
        $parameters['payerId'] = 0;
        // Check bank
        $result = $this->call('PaymentRequest', $parameters);
        if ($result['Status'] == 100) {
            header('Location: https://www.zarinpal.com/pg/StartPay/'.$result['Authority']);
        } else {
            $this->setPaymentError($result[0]);
            // set log
            $log = array();
            $log['gateway'] = $this->gatewayAdapter;
            $log['authority'] = $result[0];
            $log['value'] = Json::encode($this->gatewayInvoice);
            $log['invoice'] = $this->gatewayInvoice['id'];
            $log['amount'] = intval($this->gatewayInvoice['total_price']);
            $log['status'] = 0;
            $log['message'] = 'ERR: '.$result['Status'];
            Pi::api('log', 'order')->setLog($log);
        }
    }

    public function verifyPayment($request, $processing)
    {
        // Set result
        $result = array();
        $result['status'] = 0;
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($processing['random_id'], 'random_id');
        // Check Status
        if ($request['Status'] == 'OK') {
            // Set parameters
            $parameters = array();
            $parameters['MerchantID'] = $this->gatewayOption['MerchantID'];
            $parameters['Authority'] = $request['Authority'];
            $parameters['Amount'] = intval($invoice['total_price']);
            // Call
            $call = $this->call('PaymentVerification', $parameters);
            // Check
            if ($call['Status'] == 100) {
                $result['status'] = 1;
                // Get invoice
                $message = __('Your payment were successfully.');
                // Set log value
                $value = array();
                $value['request'] = $request;
                $value['PaymentVerification'] = $call;
                $value = Json::encode($value);
                // Set log
                $log = array();
                $log['gateway'] = $this->gatewayAdapter;
                $log['authority'] = $request['authority'];
                $log['value'] = $value;
                $log['invoice'] = $invoice['id'];
                $log['amount'] = $invoice['total_price'];
                $log['status'] = 1;
                $log['message'] = $message;
                $logResult = Pi::api('log', 'order')->setLog($log);
                // Update invoice
                if ($logResult) {
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($request['SaleOrderId']);
                    $result['status'] = 1;
                }
            } else {
                // Set log value
                $value = array();
                $value['request'] = $request;
                $value = Json::encode($value);
                // Set log
                $log = array();
                $log['gateway'] = $this->gatewayAdapter;
                $log['authority'] = $request['authority'];
                $log['value'] = $value;
                $log['invoice'] = $invoice['id'];
                $log['amount'] = $invoice['total_price'];
                $log['status'] = 0;
                $log['message'] = sprintf(__('Transation failed. Status: %s'), $result['Status']);
                Pi::api('log', 'order')->setLog($log);
            }
        } else {
            // Set log value
            $value = array();
            $value['request'] = $request;
            $value = Json::encode($value);
            // Set log
            $log = array();
            $log['gateway'] = $this->gatewayAdapter;
            $log['authority'] = $request['authority'];
            $log['value'] = $value;
            $log['invoice'] = $invoice['id'];
            $log['amount'] = $invoice['total_price'];
            $log['status'] = 0;
            $log['message'] = __('Transaction canceled by user');
            Pi::api('log', 'order')->setLog($log);
        }
        // Set result
        $result['adapter'] = $this->gatewayAdapter;
        $result['invoice'] = $invoice['id'];
        $result['order'] = $invoice['order'];
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

    public function call($api, $parameters)
    {
        // Set nusoap client
        require_once Pi::path('module') . '/order/src/Gateway/Zarinpal/nusoap.php';
        // Set client
        $client = new \nusoap_client($this->getDialogUrl());
        return $client->call($api, $parameters, $this->getNamespaceUrl());
    }
}
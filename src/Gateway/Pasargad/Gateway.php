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

namespace Module\Order\Gateway\Pasargad;

use Pi;
use Module\Order\Gateway\AbstractGateway;

use Module\Order\Gateway\Pasargad\RSAProcessor;
use Module\Order\Gateway\Pasargad\Parser;
use Module\Order\Gateway\Pasargad\Rsa;

use Zend\Json\Json;

class Gateway extends AbstractGateway
{
    public function setAdapter()
    {
        $this->gatewayAdapter = 'Pasargad';
    }

    public function setInformation()
    {
        $gateway = array();
        $gateway['title'] = __('Bank Pasargad (Iran)');
        $gateway['path'] = 'Pasargad';
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
        // form terminalCode
        $form['terminalCode'] = array(
            'name' => 'terminalCode',
            'label' => __('terminalCode'),
            'type' => 'text',
            'required' => true,
        );
        // form merchantCode
        $form['merchantCode'] = array(
            'name' => 'merchantCode',
            'label' => __('merchantCode'),
            'type' => 'text',
            'required' => true,
        );
        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        $form = array();
        // form invoiceNumber
        $form['invoiceNumber'] = array(
            'name' => 'invoiceNumber',
            'type' => 'hidden',
        );
        // form invoiceDate
        $form['invoiceDate'] = array(
            'name' => 'invoiceDate',
            'type' => 'hidden',
        );
        // form amount
        $form['amount'] = array(
            'name' => 'amount',
            'type' => 'hidden',
        );
        // form terminalCode
        $form['terminalCode'] = array(
            'name' => 'terminalCode',
            'type' => 'hidden',
        );
        // form merchantCode
        $form['merchantCode'] = array(
            'name' => 'merchantCode',
            'type' => 'hidden',
        );
        // form redirectAddress
        $form['redirectAddress'] = array(
            'name' => 'redirectAddress',
            'type' => 'hidden',
        );
        // form timeStamp
        $form['timeStamp'] = array(
            'name' => 'timeStamp',
            'type' => 'hidden',
        );
        // form action
        $form['action'] = array(
            'name' => 'action',
            'type' => 'hidden',
        );
        // form sign
        $form['sign'] = array(
            'name' => 'sign',
            'type' => 'hidden',
        );
        $this->gatewayPayForm = $form;
        return $this;
    }

    public function setRedirectUrl()
    {
        // Set parameters
        $merchantCode = $this->gatewayOption['merchantCode'];
        $terminalCode = $this->gatewayOption['terminalCode'];
        $invoiceNumber = $this->gatewayInvoice['random_id'];
        $invoiceDate = date("Y/m/d H:i:s", $this->gatewayInvoice['time_create']);
        $amount = intval($this->gatewayInvoice['total_price']);
        $redirectAddress = $this->gatewayBackUrl;
        $action = '1003';
        $timeStamp = date("Y/m/d H:i:s");
        $certificate = Pi::pach('usr/module/order/src/Gateway/Pasargad/certificate.xml');

        // Set signature
        $processor = new RSAProcessor($certificate, RSAKeyType::XMLFile);
        $data = "#" . $merchantCode . "#" . $terminalCode . "#" . $invoiceNumber . "#" . $invoiceDate . "#" . $amount . "#" . $redirectAddress . "#" . $action . "#" . $timeStamp . "#";
        $data = sha1($data, true);
        $data = $processor->sign($data);
        $sign = base64_encode($data);

        // Set form value
        $this->gatewayPayInformation['invoiceNumber'] = $invoiceNumber;
        $this->gatewayPayInformation['invoiceDate'] = $invoiceDate;
        $this->gatewayPayInformation['amount'] = $amount;
        $this->gatewayPayInformation['terminalCode'] = $merchantCode;
        $this->gatewayPayInformation['merchantCode'] = $merchantCode;
        $this->gatewayPayInformation['redirectAddress'] = $redirectAddress;
        $this->gatewayPayInformation['timeStamp'] = $timeStamp;
        $this->gatewayPayInformation['action'] = $action;
        $this->gatewayPayInformation['sign'] = $sign;

        // Set post url
        $this->gatewayRedirectUrl = 'https://pep.shaparak.ir/gateway.aspx';
    }

    public function verifyPayment($request, $processing)
    {
        // Set result
        $result = array();
        // Set parameters
        $fields = array(
            'invoiceUID' => $request['tref']
        );
        // Check Transaction Result
        $checkTransactionResult = Parser::post2https($fields, 'https://pep.shaparak.ir/CheckTransactionResult.aspx');
        $checkTransactionResult = Parser::makeXMLTree($checkTransactionResult);
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($request['iN'], 'random_id');
        $result['status'] = 0;


        $timeStamp = date("Y/m/d H:i:s");

        $fields = array(
            'MerchantCode' => $this->gatewayOption['merchantCode'],
            'TerminalCode' => $this->gatewayOption['terminalCode'],
            'InvoiceNumber' => $invoice['random_id'],
            'InvoiceDate' => date("Y/m/d H:i:s", $invoice['time_create']),
            'amount' => intval($invoice['time_create']),
            'TimeStamp' => $timeStamp,
            'sign' => '',
        );



        $processor = new RSAProcessor("certificate.xml",RSAKeyType::XMLFile);
        $data = "#". $fields['MerchantCode'] ."#". $fields['TerminalCode'] ."#". $fields['InvoiceNumber'] ."#". $fields['InvoiceDate'] ."#". $fields['amount'] ."#". $fields['TimeStamp'] ."#";
        $data = sha1($data,true);
        $data =  $processor->sign($data);
        $fields['sign'] =  base64_encode($data);




        $sendingData =  "MerchantCode=". $this->gatewayOption['merchantCode'] ."&TerminalCode=". $this->gatewayOption['terminalCode'] ."&InvoiceNumber=". $invoice['random_id'] ."&InvoiceDate=". date("Y/m/d H:i:s", $invoice['time_create']) ."&amount=". intval($invoice['time_create']) ."&TimeStamp=". $timeStamp ."&sign=".$fields['sign'];
        $verifyresult = Parser::post2https($fields,'https://pep.shaparak.ir/VerifyPayment.aspx');
        $verifyresult = Parser::makeXMLTree($verifyresult);










        // Check 
        /* if ($processing['random_id'] == $request['SaleOrderId'] && $request['ResCode'] == 0) {
            // Check bank
            $call = $this->call('bpVerifyRequest', $parameters);
            if (!is_null($call)) {
                if (is_numeric($call) && $call == 0) {
                    // Get invoice
                    $message = __('Your payment were successfully.');
                    // Set log value
                    $value = array();
                    $value['request'] = $request;
                    $value['bpVerifyRequest'] = $call;
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
                    $error = $this->setPaymentError($call);
                    // Set log value
                    $value = array();
                    $value['request'] = $request;
                    $value['bpVerifyRequest'] = $call;
                    $value = Json::encode($value);
                    // Set log
                    $log = array();
                    $log['gateway'] = $this->gatewayAdapter;
                    $log['authority'] = $request['authority'];
                    $log['value'] = $value;
                    $log['invoice'] = $invoice['id'];
                    $log['amount'] = $invoice['total_price'];
                    $log['status'] = 0;
                    $log['message'] = $error;
                    Pi::api('log', 'order')->setLog($log);
                }
            } else {
                // Set log value
                $value = array();
                $value['request'] = $request;
                $value['bpVerifyRequest'] = $call;
                $value = Json::encode($value);
                // Set log
                $log = array();
                $log['gateway'] = $this->gatewayAdapter;
                $log['authority'] = $request['authority'];
                $log['value'] = $value;
                $log['invoice'] = $invoice['id'];
                $log['amount'] = $invoice['total_price'];
                $log['status'] = 0;
                $log['message'] = __('bpVerifyRequest method is null');
                Pi::api('log', 'order')->setLog($log);
            }
        } elseif ($request['ResCode'] > 0) {
            $error = $this->setPaymentError($request['ResCode']);
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
            $log['message'] = $error;
            Pi::api('log', 'order')->setLog($log);
        } */
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
        if (empty($id)) {
            $this->gatewayError = __('Bank Pasargad error empty');
        }

        switch ($id) {
            case '':
                $error = __('Bank Pasargad error 0');
                break;

            default:
                $error = sprintf(__('Bank Pasargad error %s'), $id);
                break;
        }
        // Set error
        $this->gatewayError = $error;
        return $error;
    }
}
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

namespace Module\Order\Gateway\Dargahpardakht;

use Pi;
use Module\Order\Gateway\AbstractGateway;
use Zend\Json\Json;

class Gateway extends AbstractGateway
{
    public function setAdapter()
    {
        $this->gatewayAdapter = 'Dargahpardakht';
    }

    public function setInformation()
    {
        $gateway = array();
        $gateway['title'] = __('DargahPardakht');
        $gateway['path'] = 'Dargahpardakht';
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
            'name' => 'id',
            'label' => __('ID'),
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

    public function setRedirectUrl()
    {
        $url = 'http://dargahpardakht.com/webservice/index.php';
        $id = $this->gatewayOption['id'];
        $amount = intval($this->gatewayInvoice['total_price']) / 10;
        $callback = $this->gatewayBackUrl;
        $resnum = $this->gatewayInvoice['random_id'];
        $result = $this->dargahpardakhtSend($url, $id, $amount, $callback, $resnum);
        if ($result > 0 && is_numeric($result)) {
            $this->gatewayPayInformation['result'] = $result;
            $this->gatewayRedirectUrl = sprintf('http://dargahpardakht.com/webservice/go.php?id=%s', $result);
        } else {
            $this->setPaymentError($result);
            // set log
            $log = array();
            $log['gateway'] = $this->gatewayAdapter;
            $log['authority'] = $result;
            $log['value'] = Json::encode($this->gatewayInvoice);
            $log['invoice'] = $this->gatewayInvoice['id'];
            $log['amount'] = intval($this->gatewayInvoice['total_price']);
            $log['status'] = 0;
            $log['message'] = $this->gatewayError;
            Pi::api('log', 'order')->setLog($log);
            $this->gatewayRedirectUrl = 'http://dargahpardakht.com/webservice/go.php';
        }
    }

    public function verifyPayment($request, $processing)
    {
        // Set result
        $result = array();
        $result['status'] = 0;
        // Set request as array
        $request = $request->toArray();
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($request['resnum'], 'random_id');
        // Check status
        if ($processing['random_id'] == $request['resnum'] && $request['status'] == 1) {
            // Set information
            $url = 'http://dargahpardakht.com/webservice/verify.php';
            $id = $this->gatewayOption['id'];
            $amount = $invoice['total_price'] / 10;
            $resultDargahpardakht = $this->dargahpardakhtGet($url, $id, $request['resnum'], $request['refnum'], $amount);
            switch ($resultDargahpardakht) {
                // error
                case '-1' :
                    echo
                    // Set log value
                    $value = array();
                    $value['request'] = $request;
                    $value['result'] = $resultDargahpardakht;
                    $value = Json::encode($value);
                    // Set log
                    $log = array();
                    $log['gateway'] = $this->gatewayAdapter;
                    $log['authority'] = $request['authority'];
                    $log['value'] = $value;
                    $log['invoice'] = $invoice['id'];
                    $log['amount'] = $invoice['total_price'];
                    $log['status'] = 0;
                    $log['message'] = __('Dargahpardakht verify error -1');
                    Pi::api('log', 'order')->setLog($log);
                    break;

                // error
                case '0' :
                    // Set log value
                    $value = array();
                    $value['request'] = $request;
                    $value['result'] = $resultDargahpardakht;
                    $value = Json::encode($value);
                    // Set log
                    $log = array();
                    $log['gateway'] = $this->gatewayAdapter;
                    $log['authority'] = $request['authority'];
                    $log['value'] = $value;
                    $log['invoice'] = $invoice['id'];
                    $log['amount'] = $invoice['total_price'];
                    $log['status'] = 0;
                    $log['message'] = __('Dargahpardakht verify error 0');
                    Pi::api('log', 'order')->setLog($log);
                    break;

                // ok
                case '1' :
                    // Set log value
                    $value = array();
                    $value['request'] = $request;
                    $value['result'] = $resultDargahpardakht;
                    $value = Json::encode($value);
                    // Set log
                    $log = array();
                    $log['gateway'] = $this->gatewayAdapter;
                    $log['authority'] = $request['authority'];
                    $log['value'] = $value;
                    $log['invoice'] = $invoice['id'];
                    $log['amount'] = $invoice['total_price'];
                    $log['status'] = 1;
                    $log['message'] = __('Your payment were successfully.');
                    Pi::api('log', 'order')->setLog($log);
                    // Update invoice
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($request['resnum']);
                    $result['status'] = 1;
                    break;
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
            $log['message'] = __('Dargahpardakht verify error 0');
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
        if (empty($id)) {
            $this->gatewayError = __('Dargahpardakht error empty');
        }

        switch ($id) {
            case '-1':
                $error = __('Dargahpardakht error -1');
                break;

            case '-2':
                $error = __('Dargahpardakht error -2');
                break;

            case '-3':
                $error = __('Dargahpardakht error -3');
                break;

            case '-4':
                $error = __('Dargahpardakht error -4');
                break;

            case '-5':
                $error = __('Dargahpardakht error -5');
                break;

            case '-6':
                $error = __('Dargahpardakht error -6');
                break;

            case '-7':
                $error = __('Dargahpardakht error -7');
                break;
        }
        // Set error
        $this->gatewayError = $error;
        return $error;
    }

    public function dargahpardakhtSend($url, $id, $amount, $callback, $resnum)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'id' => $id,
            'amount' => $amount,
            'callback' => $callback,
            'resnum' => $resnum,
            'desc' => ''
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function dargahpardakhtGet($url, $id, $resnum, $refnum, $amount)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'id' => $id,
            'resnum' => $resnum,
            'refnum' => $refnum,
            'amount' => $amount
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
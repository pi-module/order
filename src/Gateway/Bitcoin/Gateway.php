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

namespace Module\Order\Gateway\Bitcoin;

use Pi;
use Module\Order\Gateway\AbstractGateway;
use Zend\Json\Json;

/*
 * https://spectrocoin.com
 * https://spectrofinance.github.io/SpectroCoin-Merchant-API
 * https://github.com/SpectroFinance/SpectroCoin-Merchant-PHP
 */
class Gateway extends AbstractGateway
{
    public function setAdapter()
    {
        $this->gatewayAdapter = 'Bitcoin';
    }

    public function setInformation()
    {
        $gateway = array();
        $gateway['title'] = __('Bitcoin (blockchain)');
        $gateway['path'] = 'Bitcoin';
        $gateway['type'] = 'online';
        $gateway['version'] = '1.0';
        $gateway['description'] = '';
        $gateway['author'] = 'Hossein Azizabadi <azizabadi@faragostaresh.com>';
        $gateway['credits'] = '@voltan';
        $gateway['releaseDate'] = 1480802565;

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
        // form merchantId
        $form['merchantId'] = array(
            'name' => 'merchantId',
            'label' => __('Merchant ID'),
            'type' => 'text',
            'required' => true,
        );
        // form apiId
        $form['apiId'] = array(
            'name' => 'apiId',
            'label' => __('Project (API) ID'),
            'type' => 'text',
            'required' => true,
        );
        // form signature
        $form['signature'] = array(
            'name' => 'signature',
            'label' => __('Signature (private key)'),
            'type' => 'textarea',
            'required' => true,
        );
        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        $form = array();
        $this->gatewayPayForm = $form;
        return $this;
    }

    public function setRedirectUrl()
    {
        // Call SCMerchantClient
        include_once Pi::path('module') . '/order/src/Gateway/Bitcoin/SCMerchantClient/SCMerchantClient.php';

        $url = sprintf('https://blockchain.info/tobtc?currency=USD&value=%s', $this->gatewayInvoice['total_price']);
        $amount = Pi::service('remote')->get($url);

        $orderId = $this->gatewayInvoice['random_id'];
        $payCurrency = 'BTC';
        $payAmount = $amount;
        $receiveCurrency = 'USD';
        $receiveAmount = $this->gatewayInvoice['total_price'];
        $description = 'Far War Art payment';
        $culture = "en";

        $scMerchantClient = new \SCMerchantClient(
            'https://spectrocoin.com/api/merchant/1',
            $this->gatewayOption['merchantId'],
            $this->gatewayOption['apiId']
        );

        $scMerchantClient->setPrivateMerchantKey($this->gatewayOption['signature']);

        $backUrl = $this->gatewayNotifyUrl . '?invoice=' . $this->gatewayInvoice['random_id'];

        $createOrderRequest = new \CreateOrderRequest(
            $orderId,
            $payCurrency,
            $payAmount,
            $receiveCurrency,
            $receiveAmount,
            $description,
            $culture,
            $backUrl,
            $backUrl,
            $backUrl
        );
        $createOrderResponse = $scMerchantClient->createOrder($createOrderRequest);

        if ($createOrderResponse instanceof \ApiError) {
            $this->gatewayError = 'Error occurred. ' . $createOrderResponse->getCode() . ': ' . $createOrderResponse->getMessage();
        } else if ($createOrderResponse instanceof \CreateOrderResponse) {
            $this->gatewayRedirectUrl = $createOrderResponse->getRedirectUrl();
        } else {
            $this->gatewayError = 'error';
        }
    }

    public function verifyPayment($request, $processing)
    {
        // Set result
        $result = array();
        $result['status'] = 0;

        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($processing['random_id'], 'random_id');

        // Set log
        $log = array();
        $log['gateway'] = $this->gatewayAdapter;
        $log['authority'] = '';
        $log['value'] = Json::encode($request);
        $log['invoice'] = $invoice['id'];
        $log['amount'] = $invoice['total_price'];
        $log['status'] = $result['status'];

        //
        $scMerchantClient = new \SCMerchantClient(
            'https://spectrocoin.com/api/merchant/1',
            $this->gatewayOption['merchantId'],
            $this->gatewayOption['apiId']
        );

        $scMerchantClient->setPrivateMerchantKey($this->gatewayOption['signature']);

        $callback = $scMerchantClient->parseCreateOrderCallback($request);
        if ($callback != null && $scMerchantClient->validateCreateOrderCallback($callback)){
            switch ($callback->getStatus()) {
                case \OrderStatusEnum::$Test:
                    $log['message'] = __('Noting paid ! Test');
                    break;

                case \OrderStatusEnum::$New:
                    $log['message'] = __('Noting paid ! New');
                    break;

                case \OrderStatusEnum::$Pending:
                    $log['message'] = __('Noting paid ! Pending');
                    break;

                case \OrderStatusEnum::$Expired:
                    $log['message'] = __('Noting paid ! Expired');
                    break;

                case \OrderStatusEnum::$Failed:
                    $log['message'] = __('Noting paid ! Failed');
                    break;

                case \OrderStatusEnum::$Paid:
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($request['invoice']);
                    $result['status'] = 1;
                    $log['status'] = 1;
                    $log['message'] = __('Your payment were successfully.');
                    break;

                default:
                    $log['message'] = 'Unknown order status: '.$callback->getStatus();
                    break;
            }

        } else {
            $log['message'] = 'Invalid callback!';
        }

        // Save log
        Pi::api('log', 'order')->setLog($log);

        // Set result
        $result['adapter'] = $this->gatewayAdapter;
        $result['invoice'] = $invoice['id'];
        $result['order'] = $invoice['order'];
        return $result;
    }

    public function setMessage($log)
    {
        return $log;
    }

    public function setPaymentError($id = '')
    {
        $this->gatewayError = $id;
    }
}
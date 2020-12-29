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

namespace Module\Order\Gateway\Bitcoin;

use Module\Order\Gateway\AbstractGateway;
use Pi;
use Laminas\Json\Json;

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
        $gateway                = [];
        $gateway['title']       = __('Bitcoin (blockchain)');
        $gateway['path']        = 'Bitcoin';
        $gateway['type']        = 'online';
        $gateway['version']     = '1.0';
        $gateway['description'] = '';
        $gateway['author']      = 'Hossein Azizabadi <azizabadi@faragostaresh.com>';
        $gateway['credits']     = '@voltan';
        $gateway['releaseDate'] = 1480802565;

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
        // form merchantId
        $form['merchantId'] = [
            'name'     => 'merchantId',
            'label'    => __('Merchant ID'),
            'type'     => 'text',
            'required' => true,
        ];
        // form apiId
        $form['apiId'] = [
            'name'     => 'apiId',
            'label'    => __('Project (API) ID'),
            'type'     => 'text',
            'required' => true,
        ];
        // form signature
        $form['signature']        = [
            'name'     => 'signature',
            'label'    => __('Signature (private key)'),
            'type'     => 'textarea',
            'required' => true,
        ];
        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        $form                 = [];
        $this->gatewayPayForm = $form;
        return $this;
    }

    public function setRedirectUrl()
    {
        // Call SCMerchantClient
        include_once Pi::path('module') . '/order/src/Gateway/Bitcoin/SCMerchantClient/SCMerchantClient.php';

        $url    = sprintf('https://blockchain.info/tobtc?currency=USD&value=%s', $this->gatewayInvoice['total_price']);
        $amount = Pi::service('remote')->get($url);

        $orderId         = $this->gatewayInvoice['random_id'];
        $payCurrency     = 'BTC';
        $payAmount       = $amount;
        $receiveCurrency = 'USD';
        $receiveAmount   = $this->gatewayInvoice['total_price'];
        $description     = 'Far War Art payment';
        $culture         = "en";

        $scMerchantClient = new \SCMerchantClient(
            'https://spectrocoin.com/api/merchant/1',
            $this->gatewayOption['merchantId'],
            $this->gatewayOption['apiId']
        );

        $scMerchantClient->setPrivateMerchantKey($this->gatewayOption['signature']);

        $backUrl = sprintf(
            '%s?gatewayName=Bitcoin&invoice=%s',
            $this->gatewayNotifyUrl,
            $this->gatewayInvoice['random_id']
        );

        $createOrderRequest  = new \CreateOrderRequest(
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
        } else {
            if ($createOrderResponse instanceof \CreateOrderResponse) {
                $this->gatewayRedirectUrl = $createOrderResponse->getRedirectUrl();
            } else {
                $this->gatewayError = 'error';
            }
        }
    }

    public function verifyPayment($request, $processing)
    {
        // Call SCMerchantClient
        include_once Pi::path('module') . '/order/src/Gateway/Bitcoin/SCMerchantClient/SCMerchantClient.php';

        // Set result
        $result           = [];
        $result['status'] = 0;

        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($processing['random_id'], 'random_id');

        // Set log
        $log              = [];
        $log['gateway']   = $this->gatewayAdapter;
        $log['authority'] = '';
        $log['value']     = Json::encode($request);
        $log['invoice']   = $invoice['id'];
        $log['amount']    = $invoice['total_price'];
        $log['status']    = $result['status'];
        $log['uid']       = $processing['uid'];
        $log['message']   = 'test1';
        Pi::api('log', 'order')->setLog($log);

        //
        $scMerchantClient = new \SCMerchantClient(
            'https://spectrocoin.com/api/merchant/1',
            $this->gatewayOption['merchantId'],
            $this->gatewayOption['apiId']
        );

        $log['message'] = 'test2';
        Pi::api('log', 'order')->setLog($log);

        $scMerchantClient->setPrivateMerchantKey($this->gatewayOption['signature']);

        $log['message'] = 'test3';
        Pi::api('log', 'order')->setLog($log);

        $callback = $scMerchantClient->parseCreateOrderCallback($request);

        $log['message'] = 'test4';
        Pi::api('log', 'order')->setLog($log);

        if ($callback != null && $scMerchantClient->validateCreateOrderCallback($callback)) {
            $log['message'] = 'Status' . $callback->getStatus();
            Pi::api('log', 'order')->setLog($log);

            switch ($callback->getStatus()) {
                case 1:
                    $log['message'] = __('Start state when order is registered in SpectroCoin system');
                    break;

                case 2:
                    $log['message'] = __('Payment (or part of it) was received but still not confirmed');
                    break;

                case 3:
                    $log['message']   = __('Order is complete');
                    $invoice          = Pi::api('invoice', 'order')->updateInvoice($request['invoice']);
                    $result['status'] = 1;
                    $log['status']    = 1;
                    break;

                case 4:
                    $log['message'] = __('Some error occurred');
                    break;

                case 5:
                    $log['message'] = __('Payment was not received in time');
                    break;

                case 6:
                    $log['message'] = __('Test order');
                    break;

                default:
                    $log['message'] = 'Unknown order status: ' . $callback->getStatus();
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
        $result['order']   = $invoice['order'];
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

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

namespace Module\Order\Gateway\Paypal;

use Pi;
use Module\Order\Gateway\AbstractGateway;
use Zend\Json\Json;

class Gateway extends AbstractGateway
{
    public function __construct()
    {
        parent::__construct();
        $this->_type = AbstractGateway::TYPE_REST;
        $this->_needToken = true;
    }
    
    public function getType()
    {
        return $this->_type; 
    }
    
    public function needToken()
    {
        return $this->_needToken;
    }
    
    public function getUrl()
    {
        if ($this->gatewayOption['test_mode']) {
            return 'https://api.sandbox.paypal.com/v1';
        } else {
            return 'https://api.paypal.com/v1';
        }
    }
    
    protected function getToken()
    {
        $key = $this->gatewayOption['username'];
        $secret = $this->gatewayOption['password'];
        $url = $this->getUrl() . '/oauth2/token';
        
        $this->setLog('token start');
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $key.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
        curl_close($ch);

        $this->setLog('token_stop : ' . $result);
        
        if(empty($result)) {
           return null;
        }
    
        $json = json_decode($result);
        return $json->access_token ;
        
    }

    public function getApproval($invoice)
    {
        $token = $this->getToken();
        $url = $this->getUrl() . '/payments/payment';
        $header =  array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        );
        
        // see here : https://developer.paypal.com/docs/integration/direct/payments/paypal-payments/#create-paypal-payment
        $data ='{
            "intent":"sale",
            "redirect_urls":{
                "return_url":"' . $this->gatewayFinishUrl . '",
                "cancel_url":"' . $this->gatewayCancelUrl . '"
            },
            "payer":{
                "payment_method":"paypal"
            },
            "transactions":[
                {
                    "amount": {
                        "total": "' . ($this->gatewayPayInformation['amount_1'] + $this->gatewayPayInformation['tax_1']) . '",
                        "currency": "' . $this->gatewayPayInformation['currency_code'] . '",
                        "details": {
                            "subtotal": "' . $this->gatewayPayInformation['amount_1'] . '",
                            "tax": "' . $this->gatewayPayInformation['tax_1'] . '"
                        }
                    },
                    "description": "",
                    "item_list": {
                        "items": [
                            {
                                "name": "' . $this->gatewayPayInformation['item_name_1'] . '",
                                "price": "' . $this->gatewayPayInformation['amount_1'] . '",
                                "currency": "' . $this->gatewayPayInformation['currency_code'] . '",
                                "quantity": "' . $this->gatewayPayInformation['quantity_1'] . '",
                                "description": "' . $this->gatewayPayInformation['item_name_1'] . '",
                                "tax": "' . $this->gatewayPayInformation['tax_1'] . '"
                            }
                        ],
                        "shipping_address": {
                            "recipient_name": "' . $this->gatewayPayInformation['first_name'] . ' ' . $this->gatewayPayInformation['last_name'] . '",
                            "line1": "' . $this->gatewayPayInformation['address1'] . '",
                            "line2": "' . $this->gatewayPayInformation['address2'] . '",
                            "city": "' . $this->gatewayPayInformation['city'] . '",
                            "state": "' . $this->gatewayPayInformation['state'] . '",
                            "phone": "' . $this->gatewayPayInformation['night_phone_b'] . '",
                            "postal_code": "' . $this->gatewayPayInformation['zip'] . '",
                            "country_code": "' . $this->gatewayOption['location'] . '"
                        }
                    }
                }
            ]
        }';
        
        $this->setLog('approval_start : ' . $data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->setLog('approval_stop : ' . $result);
        
        if(empty($result)) {
            return null;
        } else {
            $result = json_decode($result);
        }
        
        $extra = json_decode($invoice['extra']);
        $extra['paypal_payment_id'] = $result->id;
        Pi::model('invoice', 'order')->update(
            array('extra' => json_encode($extra)),
            array('id' => $invoice['id'])
        );

        foreach ($result->links as $link) {
            if ($link->rel == 'approval_url') {
               return $link->href;
            }
        }
        
        return null;
        
    }
    
    public function getPayment($paymentId)
    {
        $token = $this->getToken();
        $url = $this->getUrl() . '/payments/payment/' . $paymentId;
        $header =  array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        );
        
        $this->setLog('payment_detail_start : ' . $paymentId);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->setLog('payment_detail_stop : ' . $result);
        
        if(empty($result)) {
            return null;
        } else {
            $result = json_decode($result);
        }
        
        return $result;
        
    }
    public function execute($payerId, $paymentId)
    {
        $token = $this->getToken();
        $url = $this->getUrl() . '/payments/payment/' . $paymentId . '/execute';
        $data = '{"payer_id":"' . $payerId . '"}';
        $header =  array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        );
        if ($this->gatewayOption['error_mode']) {
            $header[] = 'PayPal-Mock-Response: {"mock_application_codes":"INSTRUMENT_DECLINED"';    
        }

        $this->setLog('execute_start : ' . $data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
    
        $this->setLog('execute_stop : ' . $result);
        
        if(empty($result)) {
            return null;
        } else {
            $result = json_decode($result);
        }
        
        return $result;
    }
    
    public function setAdapter()
    {
        $this->gatewayAdapter = 'Paypal';
    }

    public function setInformation()
    {
        $gateway = array();
        $gateway['title'] = __('Paypal');
        $gateway['path'] = 'Paypal';
        $gateway['type'] = 'online';
        $gateway['version'] = '2.0';
        $gateway['description'] = '';
        $gateway['author'] = 'Mickael STAMM <contact@sta2m.com>';
        $gateway['credits'] = '@sta2m';
        $gateway['releaseDate'] = 1510244649;
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
        // business
        $form['business'] = array(
            'name' => 'business',
            'label' => __('Paypal email address'),
            'type' => 'text',
            'required' => true,
        );
        // currency
        $form['currency'] = array(
            'name' => 'currency',
            'label' => __('Paypal currency'),
            'type' => 'text',
            'required' => true,
        );
        // cursymbol
        $form['cursymbol'] = array(
            'name' => 'cursymbol',
            'label' => __('Paypal currency symbol'),
            'type' => 'text',
            'required' => true,
        );
        // location
        $form['location'] = array(
            'name' => 'location',
            'label' => __('Location code (ex GB)'),
            'type' => 'text',
            'required' => true,
        );
        // test_mode
        $form['test_mode'] = array(
            'name' => 'test_mode',
            'label' => __('Test mode by sandbox'),
            'type' => 'checkbox',
            'required' => false,
        );
        // Username
        $form['username'] = array(
            'name' => 'username',
            'label' => __('Client ID'),
            'type' => 'text',
            'required' => false,
        );
        // password
        $form['password'] = array(
            'name' => 'password',
            'label' => __('Secret'),
            'type' => 'text',
            'required' => false,
        );
        // test_mode
        $form['error_mode'] = array(
            'name' => 'error_mode',
            'label' => __('Force error for sandbox'),
            'type' => 'checkbox',
            'required' => false,
        );
        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function getAuthority()
    {
        // Get config
        $config = Pi::service('registry')->config->read('order');
        // Get order
        $order = Pi::api('order', 'order')->getOrder($this->gatewayInvoice['order']);
        // Get product list
        $products = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Set products to payment
        $i = 1;
        foreach ($products as $product) {
            $this->gatewayPayInformation['item_name_' . $i] = $product['details']['title'];
            $this->gatewayPayInformation['item_number_' . $i] = $product['number'];
            $this->gatewayPayInformation['quantity_' . $i] = 1;
            $this->gatewayPayInformation['amount_' . $i] = $product['product_price'] - $product['discount_price'];
            $this->gatewayPayInformation['tax_' . $i] = $product['vat_price'];
            $i++;
        }
        // Set address
        $address = '';
        if ($config['order_address1'] && $config['order_address1']) {
            if (!empty($order['address1'])) {
                $address = $order['address1'];
            } elseif (!empty($order['address2'])) {
                $address = $order['address2'];
            }
        } elseif ($config['order_address1']) {
            $address = $order['address1'];
        } elseif ($config['order_address2']) {
            $address = $order['address2'];
        }
        // Set payment information
        $this->gatewayPayInformation['no_shipping'] = 1;
        $this->gatewayPayInformation['first_name'] = $order['first_name'];
        $this->gatewayPayInformation['last_name'] = $order['last_name'];
        $this->gatewayPayInformation['address1'] = $address;
        $this->gatewayPayInformation['address2'] = '';
        $this->gatewayPayInformation['address_override'] = 1;
        $this->gatewayPayInformation['city'] = $order['city'];
        $this->gatewayPayInformation['state'] = $order['state'];
        $this->gatewayPayInformation['country'] = $order['country'];
        $this->gatewayPayInformation['zip'] = $order['zip_code'];
        $this->gatewayPayInformation['email'] = $order['email'];
        $this->gatewayPayInformation['night_phone_b'] = $order['mobile'];
        $this->gatewayPayInformation['cmd'] = '_cart';
        $this->gatewayPayInformation['upload'] = 1;
        $this->gatewayPayInformation['return'] = $this->gatewayFinishUrl;
        $this->gatewayPayInformation['cancel_return'] = $this->gatewayCancelUrl;
        $this->gatewayPayInformation['notify_url'] = $this->gatewayNotifyUrl;
        $this->gatewayPayInformation['invoice'] = $this->gatewayInvoice['random_id'];
        $this->gatewayPayInformation['business'] = $this->gatewayOption['business'];
        $this->gatewayPayInformation['currency_code'] = $this->gatewayOption['currency'];
        $this->gatewayPayInformation['image_url'] = $config['payment_image'];
        // Set for test mode
        if ($this->gatewayOption['test_mode']) {
            $this->gatewayPayInformation['username'] = $this->gatewayOption['username'];
            $this->gatewayPayInformation['password'] = $this->gatewayOption['password'];
            $this->gatewayPayInformation['signature'] = $this->gatewayOption['signature'];
        }
    }

    public function setRedirectUrl()
    {
        $this->getAuthority();
    }

    public function verifyPayment($request, $processing)
    {
        $invoice = Pi::api('invoice', 'order')->getInvoice($processing['invoice']);
        $extra = $invoice['extra'];
        $paymentId = $extra['paypal_payment_id'];
        $payment = $this->getPayment($paymentId);
        
        if ($payment->state == 'approved') {
            $result['status'] = 1;
            $result['adapter'] = $this->gatewayAdapter;
            $result['invoice'] = $invoice['id'];
            $result['order'] = $invoice['order'];
        } else {
            $invoice = Pi::api('invoice', 'order')->getInvoice($request['invoice'], 'random_id');
            $result['status'] = 0;
        }
        
        return $result;
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
    
    function setPayForm()
    {
        return;
    }
}

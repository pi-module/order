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
            'name'      => 'path',
            'label'     => __('path'),
            'type'      => 'hidden',
            'required'  => true,
        );
        // business
        $form['business'] = array(
            'name'      => 'business',
            'label'     => __('Paypal email address'),
            'type'      => 'text',
            'required'  => true,
        );
        // currency
        $form['currency'] = array(
            'name'      => 'currency',
            'label'     => __('Paypal currency'),
            'type'      => 'text',
            'required'  => true,
        );
        // cursymbol
        $form['cursymbol'] = array(
            'name'      => 'cursymbol',
            'label'     => __('Paypal currency symbol'),
            'type'      => 'text',
            'required'  => true,
        );
        // location
        $form['location'] = array(
            'name'      => 'location',
            'label'     => __('Location code (ex GB)'),
            'type'      => 'text',
            'required'  => true,
        );
        // custom
        $form['custom'] = array(
            'name'      => 'custom',
            'label'     => __('Custom attribute'),
            'type'      => 'text',
            'required'  => true,
        );
        // test_mode
        $form['test_mode'] = array(
            'name'      => 'test_mode',
            'label'     => __('Test mode by sandbox'),
            'type'      => 'checkbox',
            'required'  => false,
        );
        // Username
        $form['username'] = array(
            'name'      => 'username',
            'label'     => __('Username for sandbox'),
            'type'      => 'text',
            'required'  => false,
        );
        // password
        $form['password'] = array(
            'name'      => 'password',
            'label'     => __('Password for sandbox'),
            'type'      => 'text',
            'required'  => false,
        );
        // signature
        $form['signature'] = array(
            'name'      => 'signature',
            'label'     => __('Signature for sandbox'),
            'type'      => 'text',
            'required'  => false,
        );
        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        $form = array();
        // form cmd
        $form['cmd'] = array(
            'name'      => 'cmd',
            'type'      => 'hidden',
        );
        // form upload
        $form['upload'] = array(
            'name'      => 'upload',
            'type'      => 'hidden',
        );
        // form return
        $form['return'] = array(
            'name'      => 'return',
            'type'      => 'hidden',
        );
        // form cancel_return
        $form['cancel_return'] = array(
            'name'      => 'cancel_return',
            'type'      => 'hidden',
        );
        // form notify_url
        $form['notify_url'] = array(
            'name'      => 'notify_url',
            'type'      => 'hidden',
        );
        // form business
        $form['business'] = array(
            'name'      => 'business',
            'type'      => 'hidden',
        );
        // form currency_code
        $form['currency_code'] = array(
            'name'      => 'currency_code',
            'type'      => 'hidden',
        );
        // form invoice
        $form['invoice'] = array(
            'name'      => 'invoice',
            'type'      => 'hidden',
        );
        // form item_name_1
        $form['item_name_1'] = array(
            'name'      => 'item_name_1',
            'type'      => 'hidden',
        );
        // form item_number_1
        $form['item_number_1'] = array(
            'name'      => 'item_number_1',
            'type'      => 'hidden',
        );
        // form quantity_1
        $form['quantity_1'] = array(
            'name'      => 'quantity_1',
            'type'      => 'hidden',
        );
        // form amount_1
        $form['amount_1'] = array(
            'name'      => 'amount_1',
            'type'      => 'hidden',
        );
        // form amount_1
        $form['tax_1'] = array(
            'name'      => 'tax_1',
            'type'      => 'hidden',
        );
        // first_name
        $form['first_name'] = array(
            'name'      => 'first_name',
            'type'      => 'hidden',
        );
        // last_name
        $form['last_name'] = array(
            'name'      => 'last_name',
            'type'      => 'hidden',
        );
        // address1
        $form['address1'] = array(
            'name'      => 'address1',
            'type'      => 'hidden',
        );
        // city
        $form['city'] = array(
            'name'      => 'city',
            'type'      => 'hidden',
        );
        // state
        $form['state'] = array(
            'name'      => 'state',
            'type'      => 'hidden',
        );
        // country
        $form['country'] = array(
            'name'      => 'country',
            'type'      => 'hidden',
        );
        // zip
        $form['zip'] = array(
            'name'      => 'zip',
            'type'      => 'hidden',
        );
        // email
        $form['email'] = array(
            'name'      => 'email',
            'type'      => 'hidden',
        );
        // night_phone_b
        $form['night_phone_b'] = array(
            'name'      => 'night_phone_b',
            'type'      => 'hidden',
        );
        // image_url
        $form['image_url'] = array(
            'name'      => 'image_url',
            'type'      => 'hidden',
        );
        // no_shipping
        $form['no_shipping'] = array(
            'name'      => 'no_shipping',
            'type'      => 'hidden',
        );
        // address_override
        $form['address_override'] = array(
            'name'      => 'address_override',
            'type'      => 'hidden',
        );
        // Set for test mode
        if ($this->gatewayOption['test_mode']) {
            // username
            $form['username'] = array(
                'name'      => 'username',
                'type'      => 'hidden',
            );
            // password
            $form['password'] = array(
                'name'      => 'password',
                'type'      => 'hidden',
            );
            // signature
            $form['signature'] = array(
                'name'      => 'signature',
                'type'      => 'hidden',
            );
        }
        $this->gatewayPayForm = $form;
        return $this;
    }

    public function getDialogUrl()
    {
        if ($this->gatewayOption['test_mode']) {
            return 'https://www.sandbox.paypal.com';
        } else {
            return 'https://www.paypal.com';
        }
    }

    public function getAuthority()
    {
        // Temporary solution for guide module
        if ($this->gatewayInvoice['module'] == 'guide') {
            $this->gatewayPayInformation['first_name'] = $this->gatewayInvoice['description']['first_name'];
            $this->gatewayPayInformation['last_name'] = $this->gatewayInvoice['description']['last_name'];
            $this->gatewayPayInformation['address1'] = $this->gatewayInvoice['description']['address'];
            $this->gatewayPayInformation['city'] = $this->gatewayInvoice['description']['city'];
            $this->gatewayPayInformation['state'] = '';
            $this->gatewayPayInformation['country'] = $this->gatewayInvoice['description']['country'];
            $this->gatewayPayInformation['zip'] = $this->gatewayInvoice['description']['zip'];
            $this->gatewayPayInformation['email'] = $this->gatewayInvoice['description']['email'];
            $this->gatewayPayInformation['night_phone_b'] = $this->gatewayInvoice['description']['phone'];
            $this->gatewayPayInformation['item_name_1'] = $this->gatewayInvoice['description']['title'];
            $this->gatewayPayInformation['item_number_1'] = $this->gatewayInvoice['description']['number'];
            $this->gatewayPayInformation['quantity_1'] = 1;
            $this->gatewayPayInformation['amount_1'] = $this->gatewayInvoice['description']['price'];
            $this->gatewayPayInformation['tax_1'] = $this->gatewayInvoice['description']['vat'];
            $this->gatewayPayInformation['no_shipping'] = 1;
            $this->gatewayPayInformation['address_override'] = 1;
        } else {
            $this->gatewayPayInformation['item_name_1'] = $this->gatewayInvoice['description']['title'];
            $this->gatewayPayInformation['item_number_1'] = $this->gatewayInvoice['description']['number'];
            $this->gatewayPayInformation['quantity_1'] = 1;
            $this->gatewayPayInformation['amount_1'] = $this->gatewayInvoice['amount'];
        }
        $this->gatewayPayInformation['cmd'] = '_cart';
        $this->gatewayPayInformation['upload'] = 1;
        $this->gatewayPayInformation['return'] = $this->gatewayFinishUrl;
        $this->gatewayPayInformation['cancel_return'] = $this->gatewayCancelUrl;
        $this->gatewayPayInformation['notify_url'] = $this->gatewayNotifyUrl;
        $this->gatewayPayInformation['invoice'] = $this->gatewayInvoice['random_id'];
        $this->gatewayPayInformation['business'] = $this->gatewayOption['business'];
        $this->gatewayPayInformation['currency_code'] = $this->gatewayOption['currency'];
        $this->gatewayPayInformation['image_url'] = 'https://www.envie-de-queyras.com/asset/theme-izoard/image/paypal.png';
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
        if ($this->gatewayOption['test_mode']) {
            $this->gatewayRedirectUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $this->gatewayRedirectUrl = 'https://www.paypal.com/cgi-bin/webscr';
        }
    }
    
    /**
     * Verify Payment
     *
     * Some good example for verify
     * https://developer.paypal.com/docs/classic/ipn/ht_ipn/
     * https://stackoverflow.com/questions/4848227/validate-that-ipn-call-is-from-paypal
     * http://www.emanueleferonato.com/2011/09/28/using-php-with-paypals-ipn-instant-paypal-notification-to-automate-your-digital-delivery/
     * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNIntro/
     *
     * Paypal verify method
     * Source : https://developer.paypal.com/docs/classic/ipn/ht_ipn/
    */
    public function verifyPayment($request, $processing)
    {
        // STEP 1: read POST data
        $req = 'cmd=_notify-validate';
        foreach ($request as $key => $value) {
            $req .= sprintf('&%s=%s', urldecode($key), urldecode($value));
        }
 
        // Step 2: POST IPN data back to PayPal to validate
        if ($this->gatewayOption['test_mode']) {
            $url_parsed = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $url_parsed = 'https://www.paypal.com/cgi-bin/webscr';
        }
        
        // Check by curl
        $ch = curl_init($url_parsed);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
 
        if (!($res = curl_exec($ch)) ) {
            curl_close($ch);
            return false;
            exit;
        }
        curl_close($ch);

        // STEP 3: Inspect IPN validation result and act accordingly
        if (strcmp ($res, "VERIFIED") == 0) {
            $invoice = Pi::api('invoice', 'order')->updateInvoice($request['invoice']);
            $result['status'] = 1;
            // Set log
            $log = array();
            $log['gateway'] = $this->gatewayAdapter;
            $log['authority'] = '';
            $log['value'] = Json::encode($request);
            $log['invoice'] = $invoice['id'];
            $log['amount'] = $invoice['amount'];
            $log['status'] = $result['status'];
            $log['message'] = __('Your payment were successfully.');
            Pi::api('log', 'order')->setLog($log);
        } elseif (strcmp ($res, "INVALID") == 0) {
            $invoice = Pi::api('invoice', 'order')->getInvoice($request['invoice']);
            $result['status'] = 0;
            $message = __('Error');
        }
        
        // Set result
        $result['adapter'] = $this->gatewayAdapter;
        $result['invoice'] = $invoice['id'];
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
}
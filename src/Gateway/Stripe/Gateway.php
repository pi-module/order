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

namespace Module\Order\Gateway\Stripe;

use Module\Order\Gateway\AbstractGateway;
use Pi;
use Laminas\Json\Json;

class Gateway extends AbstractGateway
{
    public function __construct()
    {
        parent::__construct();
        $this->_type = AbstractGateway::TYPE_STRIPE;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getAuthority()
    {
        // Get config
        $config = Pi::service('registry')->config->read('order');

        // Get order
        $order = Pi::api('order', 'order')->getOrder($this->gatewayOrder['id']);

        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($this->gatewayInvoice['random_id'], 'random_id');

        // Get product list
        $products = Pi::api('order', 'order')->listProduct($order['id']);

        // Set products to payment
        $i = 1;
        foreach ($products as $product) {
            if ($product['product_price'] <= 0) {
                continue;
            }
            $this->gatewayPayInformation['item_name_' . $i]      = str_replace('<br>', ' : ', $product['details']['title']);
            $this->gatewayPayInformation['item_number_' . $i]    = $product['number'];
            $this->gatewayPayInformation['quantity_' . $i]       = 1;
            $this->gatewayPayInformation['amount_' . $i]         = $product['product_price'];
            $this->gatewayPayInformation['tax_' . $i]            = $product['vat_price'];
            $this->gatewayPayInformation['discount_price_' . $i] = $product['discount_price'];
            $this->gatewayPayInformation['unconsumed_' . $i]     = $product['extra']['unconsumedPrice'];
            $this->gatewayPayInformation['special_fee_' . $i]    = $product['extra']['special_fee'];
            $this->gatewayPayInformation['id_' . $i]             = $product['product'];
            $i++;
        }

        // Set address
        $address        = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $addressCompose = '';
        if ($config['order_address1'] && $config['order_address2']) {
            if (!empty($address['address1'])) {
                $addressCompose = $address['address1'];
            } else {
                if (!empty($address['address2'])) {
                    $addressCompose = $address['address2'];
                }
            }
        } else {
            if ($config['order_address1']) {
                $addressCompose = $address['address1'];
            } else {
                if ($config['order_address2']) {
                    $addressCompose = $address['address2'];
                }
            }
        }

        // Set payment information
        $this->gatewayPayInformation['nb_product']       = $i;
        $this->gatewayPayInformation['no_shipping']      = count($products);
        $this->gatewayPayInformation['first_name']       = $address['first_name'];
        $this->gatewayPayInformation['last_name']        = $address['last_name'];
        $this->gatewayPayInformation['address1']         = $addressCompose;
        $this->gatewayPayInformation['address2']         = $address['address2'];
        $this->gatewayPayInformation['address_override'] = 1;
        $this->gatewayPayInformation['city']             = $address['city'];
        $this->gatewayPayInformation['state']            = $address['state'];
        $this->gatewayPayInformation['country']          = $address['country'];
        $this->gatewayPayInformation['zip']              = $address['zip_code'];
        $this->gatewayPayInformation['email']            = $address['email'];
        $this->gatewayPayInformation['night_phone_b']    = $address['mobile'];
        $this->gatewayPayInformation['cmd']              = '_cart';
        $this->gatewayPayInformation['upload']           = 1;
        $this->gatewayPayInformation['return']           = $this->gatewayFinishUrl;
        $this->gatewayPayInformation['cancel_return']    = $this->gatewayCancelUrl;
        $this->gatewayPayInformation['notify_url']       = $this->gatewayNotifyUrl;
        $this->gatewayPayInformation['invoice']          = $this->gatewayInvoice['random_id'];
        $this->gatewayPayInformation['business']         = $this->gatewayOption['business'];
        $this->gatewayPayInformation['currency_code']    = $this->gatewayOption['currency'];
        $this->gatewayPayInformation['image_url']        = $config['payment_image'];
        $this->gatewayPayInformation['type_payment']     = $invoice['type_payment'];
        $this->gatewayPayInformation['interval']         = 'month';

        if ($order['type_commodity'] == 'booking') {
            $extra   = json_decode($order['extra'], true);
            $item    = Pi::api('item', 'guide')->getItem($extra['item']);
            $item    = Pi::api('item', 'guide')->addPolicies($item);
            $item    = Pi::api('item', 'guide')->addBankPaymentCoordinates($item);
            $package = Pi::api('package', 'guide')->getPackageFromPeriod($item['package']);

            $this->gatewayPayInformation['gateway_id']            = $item['gateway_id'];
            $this->gatewayPayInformation['booking_payment_delay'] = $item['booking_payment_delay'];

            if ($package['commission']) {
                $commission = $item['commission_percentage_owner_fullcommission'];
            } else {
                $commission = $item['commission_percentage_owner_withsubscription'];
            }

            if ((int)$commission == null) {
                $business = Pi::api('business', 'guide')->getBusiness($item['business']);
                if ($package['commission']) {
                    $commission = $business['commission_percentage_owner_fullcommission'];
                } else {
                    $commission = $business['commission_percentage_owner_withsubscription'];
                }
            }
            if ($commission < $this->gatewayOption['commission_owner_min']) {
                $commission = $this->gatewayOption['commission_owner_min'];
            }
            $this->gatewayPayInformation['commission_percentage_owner'] = $commission;
        }
    }

    public function getSession($order)
    {
        \Stripe\Stripe::setApiKey($this->gatewayOption['password']);
        if ($this->gatewayOption['api_version']) {
            \Stripe\Stripe::setApiVersion($this->gatewayOption['api_version']);
        }
        $items                   = [];
        $subtotal                = 0;
        $subtotalCommissionOwner = 0;
        $feeCustomer             = 0;
        $tax                     = 0;
        $touristTax              = 0;
        $firstPaid               = true;
        $installments            = Pi::api('installment', 'order')->getInstallmentsFromOrder($order['id']);
        foreach ($installments as $installment) {
            if ($installment['status_invoice'] != \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED) {
                if ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID) {
                    $firstPaid = false;
                }
                if ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID) {
                    if (count($installments) > 1 && $order['type_commodity'] == 'booking') {
                        $item    = [];
                        $extra   = json_decode($order['extra'], true);
                        $itemObj = Pi::model("item", 'guide')->find($extra['values']['item'], 'id');

                        $name                = $installment['count'] == 1
                            ? sprintf(
                                __("Booking %s from %s to %s - First Installment"),
                                $itemObj['title'],
                                _date(strtotime($extra['values']['date_start'])),
                                _date(strtotime($extra['values']['date_end']))
                            )
                            : sprintf(
                                __("Booking %s from %s to %s - Second Installment"),
                                $item['title'],
                                _date(strtotime($extra['values']['date_start'])),
                                _date(strtotime($extra['values']['date_end']))
                            );
                        $item["name"]        = $name;
                        $item["description"] = $name;
                        $item["amount"]      = $installment['due_price'] * 100;
                        $item["currency"]    = $this->gatewayPayInformation['currency_code'];
                        $item["quantity"]    = 1;
                        $items[]             = $item;
                    }
                    break;
                }
            }
        }

        for ($i = 1; $i < $this->gatewayPayInformation['nb_product']; ++$i) {
            $item                = [];
            $item["name"]        = addcslashes($this->gatewayPayInformation['item_name_' . $i], '"');
            $item["amount"]      = ($this->gatewayPayInformation['amount_' . $i] + $this->gatewayPayInformation['tax_' . $i]
                    - $this->gatewayPayInformation['discount_price_' . $i] - $this->gatewayPayInformation['unconsumed_' . $i]) * 100;
            $item["currency"]    = $this->gatewayPayInformation['currency_code'];
            $item["quantity"]    = $this->gatewayPayInformation['quantity_' . $i];
            $item["description"] = addcslashes($this->gatewayPayInformation['item_name_' . $i], '"');

            $totalProduct = $this->gatewayPayInformation['amount_' . $i] - $this->gatewayPayInformation['discount_price_' . $i]
                - $this->gatewayPayInformation['unconsumed_' . $i];

            if (!$this->gatewayPayInformation['special_fee_' . $i]) {
                $subtotalCommissionOwner += $totalProduct;
            }
            if ($this->gatewayPayInformation['id_' . $i] == 'touristtax') {
                $touristTax += $this->gatewayPayInformation['amount_' . $i];
            }
            if ($this->gatewayPayInformation['id_' . $i] == 'commission') {
                $feeCustomer += $totalProduct + $this->gatewayPayInformation['tax_' . $i];
            }

            $subtotal += $totalProduct;
            $tax      += $this->gatewayPayInformation['tax_' . $i];

            if (count($installments) == 1) {
                $items[] = $item;
            }

            $names[] = $item["name"];
        }

        //
        $data = [
            'payment_method_types' => ['card'],
            'customer_email'       => $this->gatewayPayInformation['email'],
            'line_items'           => $items,
            'success_url'          => $this->gatewayPayInformation['return'] . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->gatewayPayInformation['cancel_return'],
            'client_reference_id'  => 'order-' . $order['id'] . '-uid-' . $order['uid'],
        ];

        if (isset($this->gatewayPayInformation['gateway_id'])) {
            $config = Pi::service('registry')->config->read('guide');

            $feeOwner                    = round(
                ($subtotalCommissionOwner * $this->gatewayPayInformation['commission_percentage_owner']) * ((100 + $config['package_vat']) / 100)
            );
            $feeCustomer                 = $feeCustomer * 100;
            $touristTax                  = $touristTax * 100;
            $totalFee                    = $firstPaid ? $feeOwner + $feeCustomer + $touristTax : 0;
            $data['payment_intent_data'] = [
                'on_behalf_of'  => $this->gatewayPayInformation['gateway_id'],
                'transfer_data' => [
                    'destination' => $this->gatewayPayInformation['gateway_id'],
                ],
                'metadata'      =>
                    [
                        'order'        => $order['uid'],
                        'ht'           => $subtotal,
                        'vat'          => $tax,
                        'fee_owner'    => $feeOwner,
                        'fee_customer' => $feeCustomer,
                        'total_fee'    => $totalFee,
                    ],
            ];
            if ($totalFee > 0) {
                $data['payment_intent_data']['application_fee_amount'] = $totalFee;
            }

            if ($this->gatewayPayInformation['booking_payment_delay'] && $firstPaid) {
                $data['payment_intent_data']['capture_method'] = 'manual';
            }
        }
        $session = \Stripe\Checkout\Session::create($data);

        return $session;
    }

    public function setAdapter()
    {
        $this->gatewayAdapter = 'Stripe';
    }

    public function setInformation()
    {
        $gateway = [
            'title'       => __('Stripe'),
            'path'        => 'Stripe',
            'type'        => 'online',
            'version'     => '1.0',
            'description' => '',
            'author'      => 'Mickael STAMM <contact@sta2m.com>',
            'credits'     => '@sta2m',
            'releaseDate' => 1567692940,
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

        $form['onemail'] = [
            'name'     => 'onemail',
            'label'    => __('Send only 1 mail after payment (only for end user, not for admin)'),
            'type'     => 'checkbox',
            'required' => false,
        ];

        // business
        $form['business'] = [
            'name'     => 'business',
            'label'    => __('Stripe email address'),
            'type'     => 'text',
            'required' => true,
        ];

        $form['invoice_name'] = [
            'name'     => 'invoice_name',
            'label'    => __('Gateway Name on the Invoice'),
            'type'     => 'text',
            'required' => false,
        ];
        // currency
        $form['currency'] = [
            'name'     => 'currency',
            'label'    => __('Stripe currency'),
            'type'     => 'text',
            'required' => true,
        ];

        // Username
        $form['username'] = [
            'name'     => 'username',
            'label'    => __('Public key'),
            'type'     => 'text',
            'required' => false,
        ];

        // password
        $form['password'] = [
            'name'     => 'password',
            'label'    => __('Secret key'),
            'type'     => 'text',
            'required' => false,
        ];

        $form['api_version'] = [
            'name'       => 'api_version',
            'label'      => __('API Version'),
            'attributes' => [
                'description' => __("Use Stripe Format as their changelog doc"),
            ],
            'type'       => 'text',
            'required'   => false,
        ];


        $form['commission_owner_min'] = [
            'name'       => 'commission_owner_min',
            'label'      => __('Commission owner minimum'),
            'attributes' => [
                'description' => __("Use dots for decimal numbers - no comma"),
            ],
            'type'       => 'text',
            'required'   => false,
        ];

        $form['commission_customer_min'] = [
            'name'       => 'commission_customer_min',
            'label'      => __('Commission customer minimum'),
            'attributes' => [
                'description' => __("Use dots for decimal numbers - no comma"),
            ],
            'type'       => 'text',
            'required'   => false,
        ];

        $this->gatewaySettingForm = $form;
        return $this;
    }

    public function setPayForm()
    {
        return;
    }

    public function setRedirectUrl()
    {
        $this->getAuthority();
    }

    public function verifyPayment($request, $processing)
    {
        \Stripe\Stripe::setApiKey($this->gatewayOption['password']);
        if ($this->gatewayOption['api_version']) {
            \Stripe\Stripe::setApiVersion($this->gatewayOption['api_version']);
        }
        $session = \Stripe\Checkout\Session::Retrieve($request['stripe_session_id']);
        $pi      = $session['payment_intent'];
        $payment = \Stripe\PaymentIntent::retrieve($pi);


        if ($payment['status'] == 'succeeded' || $payment['status'] == 'requires_capture') {
            $order = Pi::api('order', 'order')->getOrder($processing['order']);

            $extra = json_decode($order['extra'], true);

            $result['status']  = $payment['status'] == 'succeeded' ? 1 : 2;
            $result['adapter'] = $this->gatewayAdapter;
            $result['order']   = $order['id'];
            $extra['stripe']   = [
                'metadata'       => $payment['charges']['data'][0]['metadata']->toArray(),
                'customer'       => $payment->customer,
                'payment_method' => $payment->payment_method,
                'destination'    => $payment['transfer_data']['destination'],
            ];
            $orderRow          = Pi::model('order', 'order')->find($order['id']);
            $orderRow->extra   = json_encode($extra);
            $orderRow->save();

            $installments = Pi::api('installment', 'order')->getInstallmentsFromOrder($order['id']);
            foreach ($installments as $installment) {
                $extra = json_decode($installment['extra'], true);
                if (!isset($extra['stripe'])) {
                    $extra['stripe']       = [
                        'payment_intent' => $payment['id'],
                        'transfer'       => $payment['charges']['data'][0]['transfer'],
                    ];
                    $installmentRow        = Pi::model('invoice_installment', 'order')->find($installment['id']);
                    $installmentRow->extra = json_encode($extra);
                    $installmentRow->save();

                    break;
                }
            }
        } else {
            $result['status']   = 0;
            $result['state']    = $payment['status'];
            $this->gatewayError = __('An error occured with Stripe. Please contact administrator');
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

    public function getDescription()
    {
        return __(
            'To install the Stripe Driver : <br> Get yours credentials on stripe. <br> You can use the developer mode with developers keys provided by stripe for your test and also check the error mode (no payment), to fine tune your code<br>'
        );
    }
}

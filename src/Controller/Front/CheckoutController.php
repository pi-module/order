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

namespace Module\Order\Controller\Front;

use Module\Order\Form\AddressFilter;
use Module\Order\Form\AddressForm;
use Module\Order\Form\OrderFilter;
use Module\Order\Form\OrderForm;
use Module\Order\Form\OrderSimpleFilter;
use Module\Order\Form\OrderSimpleForm;
use Module\Order\Form\PromoCheckoutFilter;
use Module\Order\Form\PromoCheckoutForm;
use Module\Order\Gateway\AbstractGateway;
use Module\System\Form\LoginFilter;
use Module\System\Form\LoginForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Laminas\Json\Json;

//use Pi\Authentication\Result;


class CheckoutController extends IndexController
{
    public function indexAction()
    {
        // Set check
        $check       = false;
        $editAddress = false;

        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Favourites addresses
        $addresses = Pi::api('customerAddress', 'order')->findAddresses();
        if (count($addresses)) {
            if (
                isset($_SESSION['order'])
                && (!isset($_SESSION['order']['delivery_address'])
                    || !isset($addresses[$_SESSION['order']['delivery_address']])
                    || !$addresses[$_SESSION['order']['delivery_address']])
            ) {
                $favouriteDelivery = Pi::api('customerAddress', 'order')->getFavouriteDelivery();
                if ($favouriteDelivery == null) {
                    $favouriteDelivery = current($addresses);
                }
                $_SESSION['order']['delivery_address'] = $favouriteDelivery['id'];
            }

            if (
                !isset($_SESSION['order']['invoicing_address'])
                || !isset($addresses[$_SESSION['order']['invoicing_address']])
                || !$addresses[$_SESSION['order']['invoicing_address']]
            ) {
                $favouriteInvoicing = Pi::api('customerAddress', 'order')->getFavouriteInvoicing();
                if ($favouriteInvoicing == null) {
                    $favouriteInvoicing = current($addresses);
                }
                $_SESSION['order']['invoicing_address'] = $favouriteInvoicing['id'];
            }
        } else {
            $_SESSION['order']['delivery_address']  = 0;
            $_SESSION['order']['invoicing_address'] = 0;
        }

        // Set cart
        $cart = Pi::api('order', 'order')->getOrderInfo();

        // Set products
        if (isset($cart['product']) && count($cart['product'])) {
            foreach ($cart['product'] as $key => $product) {
                $cart['product'][$key]['details'] = Pi::api('order', $cart['module_name'])->getProductDetails(
                    $product['product'],
                    json_decode($product['extra'], true)
                );

                $cart['product'][$key]['product_price']  = isset($product['product_price']) ? str_replace(',', '.', $product['product_price']) : 0;
                $cart['product'][$key]['discount_price'] = isset($product['discount_price']) ? str_replace(',', '.', $product['discount_price']) : 0;
                $cart['product'][$key]['shipping_price'] = isset($product['shipping_price']) ? str_replace(',', '.', $product['shipping_price']) : 0;
                $cart['product'][$key]['packing_price']  = isset($product['packing_price']) ? str_replace(',', '.', $product['packing_price']) : 0;
                $cart['product'][$key]['setup_price']    = isset($product['setup_price']) ? str_replace(',', '.', $product['setup_price']) : 0;
                $cart['product'][$key]['vat_price']      = isset($product['vat_price']) ? str_replace(',', '.', $product['vat_price']) : 0;

                $cart['product'][$key]['product_price_view'] = Pi::api('api', 'order')->viewPrice($product['product_price']);
            }
        }

        // Update order info
        Pi::api('order', 'order')->setOrderInfo($cart);

        // Check order anonymous
        if (!Pi::service('authentication')->hasIdentity() && !$config['order_anonymous']) {
            $url = ['route' => 'home'];
            $this->jump($url);
        }

        // Check cart not empty
        if (empty($cart)) {
            $url = ['', 'module' => $this->params('module'), 'controller' => 'index'];
            $this->jump($url, __('Your cart is empty.'), 'error');
        }

        // Check order is active or inactive
        if (!$config['order_active']) {
            $url = ['', 'module' => $this->params('module'), 'controller' => 'index'];
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }

        // Check user
        if (!Pi::service('authentication')->hasIdentity()) {
            if (Pi::service('module')->isActive('user')
                && isset($_SESSION['session_order'])
                && !empty($_SESSION['session_order'])
            ) {
                // Load language
                Pi::service('i18n')->load(['module/system', 'default']);
                Pi::service('i18n')->load(['module/user', 'default']);

                // Set login form
                $formLogin = new LoginForm('login');
                $formLogin->setAttribute(
                    'action',
                    $this->url('user', ['module' => 'user', 'controller' => 'login', 'action' => 'process'])
                );
                $formLogin->setData(['redirect' => $this->url('', ['controller' => 'checkout', 'action' => 'index'])]);
                $this->view()->assign('formLogin', $formLogin);
            } else {
                $this->jump(Pi::url(), __('Your cart is empty.'), 'error');
            }
        }

        // Get address
        $addressDelivery  = Pi::api('customerAddress', 'order')->getAddress($cart['delivery_address']);
        $addressInvoicing = Pi::api('customerAddress', 'order')->getAddress($cart['invoicing_address']);

        // Set pay all
        $payAll = false;
        if (isset($cart['extra']['values']['pay_all']) && !empty($cart['extra']['values']['pay_all'])) {
            $payAll = $cart['extra']['values']['pay_all'];
        }

        // Set total shipping
        if ($config['order_location_delivery']
            && isset($addressDelivery['delivery'])
            && intval($addressDelivery['delivery']) > 0
            && isset($addressDelivery['location'])
            && intval($addressDelivery['location']) > 0
        ) {
            $cart['total_shipping'] = Pi::api('delivery', 'order')->getPrice($addressDelivery['location'], $addressDelivery['delivery']);
        }

        // Set price
        $price = $this->updatePrice($cart);

        // Get installment composition from source module
        $composition = Pi::api('order', $cart['module_name'])->getInstallmentComposition($cart, true);

        // Set form option
        $option = [
            'type_commodity'    => isset($cart['type_commodity']) ? $cart['type_commodity'] : null,
            'addresses'         => $addresses,
            'delivery_address'  => $_SESSION['order']['delivery_address'],
            'invoicing_address' => $_SESSION['order']['invoicing_address'],
            'pay_all'           => $payAll,
            'composition'       => $composition,
            'due_price'         => number_format($price['total'], 2, '.', ''),
        ];

        // Set installment composition
        if (!$payAll && $composition[0] < 100) {
            $extra     = json_decode($cart['product'][0]['extra'], true);
            $item      = Pi::api('item', 'guide')->getItem($extra['item']);
            $item      = Pi::api('item', 'guide')->addPolicies($item);
            $business  = Pi::api('business', 'guide')->getBusiness($item['business']);
            $condition = Pi::api('item', 'guide')->getCancelCondition($business, $item);
            $limitDate = strtotime($cart['extra']['values']['date_start'] . ' - ' . $condition['time_limit_2'] . ' DAYS');

            $option['limit_date'] = $limitDate;
        }

        // Set form
        $formOrderSimple = new OrderSimpleForm('order', $option);
        $formOrderSimple->setInputFilter(new OrderSimpleFilter($option));

        // Set form
        $formOrder = new OrderForm('order', $option);
        $formOrder->setInputFilter(new OrderFilter($option));

        // promotion code
        $msgPromoCode      = null;
        $hasActiveCode     = Pi::api('promocode', 'order')->hasActiveCode();
        $formPromoCheckout = null;
        if ($hasActiveCode) {
            $formPromoCheckout = new PromoCheckoutForm('promoCheckout', $option);
            $formPromoCheckout->setInputFilter(new PromoCheckoutFilter($option));
        }

        // Set address form
        $formAddress = null;
        if (!count($addresses)) {

            // Set user
            $user = [];
            if (Pi::user()->getId()) {
                $user = Pi::api('user', 'user')->get(
                    Pi::user()->getId(),
                    ['email', 'first_name', 'last_name', 'address1', 'address2', 'city', 'zip_code', 'country', 'state', 'mobile', 'phone'],
                    true,
                    true
                );
            }

            // Set form
            $formAddress = new AddressForm('address', $option);
            $formAddress->setInputFilter(new AddressFilter($option));
            $formAddress->setData($user);
        }

        // Check address
        $check          = count($addresses) == 0 ? true : false;
        $invalidAddress = false;
        if ((isset($addressDelivery['account_type']) && $addressDelivery['account_type'] == 'none')
            || (isset($addressInvoicing['account_type']) && $addressInvoicing['account_type'] == 'none')
        ) {
            $invalidAddress = true;
        }

        // Check post
        if ($this->request->isPost() && !$invalidAddress) {
            $data = $this->request->getPost();

            // Save address
            if (isset($data['submit_address'])) {
                $formAddress->setData($data);
                if ($formAddress->isValid()) {

                    // Check user information
                    $values                = $formAddress->getData();
                    $values['time_create'] = time();
                    $values['uid']         = Pi::user()->getId();
                    //$values['last_name']   = isset($values['last_name']) ? strtoupper($values['last_name']) : '';
                    //$values['city']        = isset($values['city']) ? strtoupper($values['city']) : '';
                    $values['last_name'] = isset($values['last_name']) ? $values['last_name'] : '';
                    $values['city']      = isset($values['city']) ? $values['city'] : '';
                    $birthday            = explode('/', $values['birthday']);
                    $values['birthday']  = strtotime($birthday[2] . '-' . $birthday[1] . '-' . $birthday[0]);

                    if ($values['address_id'] == 0) {
                        Pi::api('customerAddress', 'order')->addAddress($values);
                    } else {
                        Pi::api('customerAddress', 'order')->updateAddress($values);
                    }
                    $url = $this->url('', ['controller' => 'checkout', 'action' => 'index']);
                    $this->jump($url);
                } else {
                    $check = true;
                }
            } else {
                // Save order
                if (isset($data['submit_order_simple'])) {
                    $formOrderSimple->setData($data);
                    if ($formOrderSimple->isValid()) {
                        $uid = Pi::user()->getId();
                        //$user = Pi::api('user', 'order')->getUserInformation();

                        // Set values
                        $values           = $formOrderSimple->getData();
                        $addressDelivery  = $addresses[$values['address_delivery_id']];
                        $addressInvoicing = $addresses[$values['address_invoicing_id']];

                        // Save order
                        $this->saveOrder($values, $addressDelivery, $addressInvoicing, $cart, $config, $uid);

                        // Set address
                        Pi::api('customerAddress', 'order')->updateFavouriteDelivery($_SESSION['order']['delivery_address']);
                        Pi::api('customerAddress', 'order')->updateFavouriteInvoicing($_SESSION['order']['invoicing_address']);
                    }
                } else {
                    // Register user and save order
                    $formOrder->setData($data);

                    if ($formOrder->isValid()) {
                        $values = $formOrder->getData();

                        // Create user account
                        $values = $this->createUserAccount($values);

                        if (!empty($values)) {

                            // Set address
                            if ($values['address_id'] == 0) {
                                Pi::api('customerAddress', 'order')->addAddress($values);
                            } else {
                                Pi::api('customerAddress', 'order')->updateAddress($values);
                            }
                            $addresses = Pi::api('customerAddress', 'order')->findAddresses($values['uid']);
                            $address   = current($addresses);

                            // Set default gateway
                            $gatewayList               = Pi::api('gateway', 'order')->getActiveGatewayName();
                            $gatewayList               = array_keys($gatewayList);
                            $values['default_gateway'] = array_shift($gatewayList);

                            // Save order
                            $this->saveOrder($values, $address, $address, $cart, $config, $values['uid']);
                        }
                    }
                }
            }
        }

        // Set new form
        $forms                  = [];
        $user                   = Pi::api('user', 'order')->getUserInformation();
        $user['address_id']     = 0;
        $forms['promoCheckout'] = $formPromoCheckout;
        $forms['new']           = $formAddress;

        // Set address forms
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $key                = sprintf('address-%s', $address['id']);
                $option['location'] = $address['location'];
                unset($address['delivery']);
                unset($address['user_note']);
            }
        }
        if (Pi::service('authentication')->hasIdentity()) {
            if ($invalidAddress) {
                $elements = $formOrderSimple->getElements();
                foreach ($elements as $element) {
                    $formOrderSimple->get($element->getName())->setAttribute('disabled', true);
                }
            }
            $forms['order'] = $formOrderSimple;
            $forms['new']   = $formAddress;
        } else {
            $forms['new'] = $formOrder;
        }

        // Get credit
        /* if ($config['credit_active'] && Pi::user()->getId() > 0) {
            $credit = $this->getModel('credit')->find(Pi::user()->getId(), 'uid')->toArray();
            $credit['amount_view'] = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view'] = ($credit['time_update'] > 0) ? _date($credit['time_update']) : __('Never update');
            $this->view()->assign('credit', $credit);
        } */

        $payAll = isset($cart['extra']['values']['pay_all']) ? $cart['extra']['values']['pay_all'] : false;
        if (!$payAll) {
            $composition = Pi::api('order', $cart['module_name'])->getInstallmentComposition($cart, true);
        }

        // Set view
        $this->view()->setTemplate('checkout');
        $this->view()->assign('forms', $forms);
        $this->view()->assign('cart', $cart);
        $this->view()->assign('price', $price);
        $this->view()->assign('config', $config);
        $this->view()->assign('addresses', $addresses);
        $this->view()->assign('check', $check);
        $this->view()->assign('editAddress', $editAddress);
        $this->view()->assign('msgPromoCode', $msgPromoCode);
        $this->view()->assign('addressDelivery', $addressDelivery);
        $this->view()->assign('addressInvoicing', $addressInvoicing);
        $this->view()->assign('invalidAddress', $invalidAddress);
    }

    public function changeAddressAction()
    {
        $type = $this->params('type');
        $id   = $this->params('id');

        // Get address
        if ($type == 'delivery') {
            $_SESSION['order']['delivery_address'] = $id;
        }
        if ($type == 'invoicing') {
            $_SESSION['order']['invoicing_address'] = $id;
        }

        // jump
        $this->jump(['', 'module' => 'order', 'controller' => 'checkout', 'action' => 'index']);
    }

    public function addressListAction()
    {
        $type = $this->params('type');

        // Get address
        $addresses = Pi::api('customerAddress', 'order')->findAddresses();
        $this->view()->setTemplate('checkout-listaddress');
        $this->view()->assign('addresses', $addresses);
        $this->view()->assign('type', $type);
    }

    public function addressAction()
    {
        // Set id
        $id = $this->params('id');

        // Set option
        $option = [];

        // Set form
        $form = new AddressForm('address', $option);
        $form->setAttribute(
            'action',
            Pi::url(
                Pi::service('url')->assemble(
                    'order', [
                        'module'     => 'order',
                        'controller' => 'checkout',
                        'action'     => 'address',
                        'id'         => $id,
                    ]
                )
            )
        );
        $form->remove('submit_address');
        $form->setInputFilter(new AddressFilter($option));

        // Check form post
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setData($data);
            if ($form->isValid()) {

                // Check user informations
                $uid  = Pi::user()->getId();
                $user = Pi::api('user', 'order')->getUserInformation();

                $values                = $form->getData();
                $values['time_create'] = time();
                $birthday              = explode('/', $values['birthday']);
                $values['birthday']    = strtotime($birthday[2] . '-' . $birthday[1] . '-' . $birthday[0]);
                $values['uid']         = $uid;
                //$values['last_name']   = strtoupper($values['last_name']);
                //$values['city']        = strtoupper($values['city']);

                if ($values['address_id'] == 0) {
                    $address                             = Pi::api('customerAddress', 'order')->addAddress($values);
                    $_SESSION['order'][$id . '_address'] = $address['id'];
                } else {
                    Pi::api('customerAddress', 'order')->updateAddress($values);
                }
                $this->flashMessenger()->addMessage(__('Address saved successfully.'));

                header('Content-Type: application/json');
                return ['status' => 1];
            }
        } else {
            if ($id && is_numeric($id)) {
                $form = new AddressForm('address', $option, $id);
                $form->setAttribute(
                    'action',
                    Pi::url(Pi::service('url')->assemble('order', ['module' => 'order', 'controller' => 'checkout', 'action' => 'address', 'id' => $id]))
                );
                $form->remove('submit_address');
                $form->setInputFilter(new AddressFilter($option));
                $values               = Pi::api('customerAddress', 'order')->getAddress($id);
                $values['address_id'] = $id;
                if ($values['birthday'] == 0) {
                    $values['birthday'] = null;
                } else {
                    $values['birthday'] = date('d/m/Y', $values['birthday']);
                }

                $form->setData($values);
            } else {
                // Get user base info
                $user = [];
                if (Pi::user()->getId()) {
                    $user = Pi::api('user', 'user')->get(
                        Pi::user()->getId(),
                        ['email', 'first_name', 'last_name', 'address1', 'address2', 'city', 'zip_code', 'country', 'state', 'mobile', 'phone'],
                        true,
                        true
                    );
                }
                $form->setData($user);
            }
        }

        $this->view()->setTemplate('checkout-address');
        $this->view()->assign('form', $form);
    }

    public function installmentAction()
    {
        // Check user
        $this->checkUser();

        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set cart
        $cart = Pi::api('order', 'order')->getOrderInfo();
        if (empty($cart)) {
            $url = ['', 'module' => $this->params('module'), 'controller' => 'index'];
            $this->jump($url, __('Your cart is empty.'), 'error');
        }

        // Check order is active or inactive
        if (!$config['order_active']) {
            $url = ['', 'module' => $this->params('module'), 'controller' => 'index'];
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }

        // check post
        if ($this->request->isPost()) {
            // Get from post
            $data = $this->request->getPost();
            $data = $data->toArray();

            // Update order info
            Pi::api('order', 'order')->updateOrderInfo($data);

            // Go to checkout
            $url = ['', 'controller' => 'checkout', 'action' => 'index'];
            $this->jump($url, __('Your installment plan save, please complete your information and payment.'));
        } else {
            // Set user
            $user = Pi::api('user', 'order')->getUserInformation();

            // Set price
            $price                   = [];
            $price['product_price']  = 0;
            $price['discount_price'] = 0;
            $price['shipping_price'] = 0;
            $price['setup_price']    = 0;
            $price['packing_price']  = 0;
            $price['vat_price']      = 0;
            $price['total_price']    = 0;

            // Check order price
            if (!empty($cart['product'])) {
                foreach ($cart['product'] as $product) {
                    $unconsumedPrice = json_decode($product['extra'], true)['unconsumedPrice'];

                    // Set price
                    $price['product_price']  = $product['product_price'] + $price['product_price'];
                    $price['discount_price'] = $product['discount_price'] + $price['discount_price'];
                    $price['shipping_price'] = $product['shipping_price'] + $price['shipping_price'];
                    $price['setup_price']    = $product['setup_price'] + $price['setup_price'];
                    $price['packing_price']  = $product['packing_price'] + $price['packing_price'];
                    $price['vat_price']      = $product['vat_price'] + $price['vat_price'];
                    // Set total
                    $total                = ((
                                $product['product_price'] +
                                $product['shipping_price'] +
                                $product['packing_price'] +
                                $product['setup_price'] +
                                $product['vat_price']
                            ) - $product['discount_price'] - $unconsumedPrice) * $product['number'];
                    $price['total_price'] = $total + $price['total_price'];
                }
            }

            // Set installment
            $installments = Pi::api('installment', 'order')->setPriceForView($price['total_price'], $user);

            // Set view
            $this->view()->setTemplate('installment');
            $this->view()->assign('cart', $cart);
            $this->view()->assign('price', $price);
            $this->view()->assign('installments', $installments);
            $this->view()->assign('user', $user);
            $this->view()->assign('config', $config);
        }
    }

    public function levelAction()
    {
        // Get info from url
        $id      = $this->params('id');
        $process = $this->params('process');
        $module  = $this->params('module');
        $data    = [];
        $return  = [
            'status' => 0,
            'data'   => $data,
        ];

        // Check user
        if (!Pi::service('authentication')->hasIdentity()) {
            if (!Pi::service('module')->isActive('user')
                && !isset($_SESSION['session_order'])
                && empty($_SESSION['session_order'])
            ) {
                return $return;
            }
        }

        // Get config
        $config = Pi::service('registry')->config->read($module);

        // process
        switch ($process) {
            case 'location':
                if ($id) {
                    // Set location
                    $location                               = $this->getModel('location')->find($id)->toArray();
                    $_SESSION['checkout']['location']       = $location['id'];
                    $_SESSION['checkout']['location_title'] = $location['title'];

                    // Get location
                    $where  = ['location' => $id];
                    $select = $this->getModel('location_delivery')->select()->where($where);
                    $rowset = $this->getModel('location_delivery')->selectWith($select);
                    foreach ($rowset as $row) {
                        $delivery = $this->getModel('delivery')->find($row->delivery)->toArray();
                        if ($delivery['status']) {
                            $data[$row->id]           = $row->toArray();
                            $data[$row->id]['title']  = $delivery['title'];
                            $data[$row->id]['status'] = $delivery['status'];
                        }
                    }

                    // Set return
                    $return['status']   = 1;
                    $return['data']     = $data;
                    $return['location'] = $location['title'];
                }
                break;

            case 'delivery':
                if ($id) {
                    // Set delivery
                    $delivery                               = $this->getModel('delivery')->find($id)->toArray();
                    $_SESSION['checkout']['delivery']       = $delivery['id'];
                    $_SESSION['checkout']['delivery_title'] = $delivery['title'];

                    // Get location_delivery
                    $location = $_SESSION['checkout']['location'];
                    $where    = ['location' => $location, 'delivery' => $id];
                    $select   = $this->getModel('location_delivery')->select()->where($where)->limit(1);
                    $row      = $this->getModel('location_delivery')->selectWith($select)->current();

                    // Set shipping price
                    $_SESSION['checkout']['shipping'] = $row->price;

                    // Get delivery_payment
                    $where  = ['delivery' => $id];
                    $select = $this->getModel('delivery_gateway')->select()->where($where);
                    $rowset = $this->getModel('delivery_gateway')->selectWith($select);
                    foreach ($rowset as $row) {
                        if ($row->gateway == 'offline') {
                            $data['payment'][$row->id]['title']  = 'Offline';
                            $data['payment'][$row->id]['path']   = 'offline';
                            $data['payment'][$row->id]['status'] = 1;
                        } else {
                            $gateway = Pi::api('gateway', 'order')->getGatewayInfo($row->gateway);
                            if ($gateway['status']) {
                                $data['payment'][$row->id]['title']  = $gateway['title'];
                                $data['payment'][$row->id]['path']   = $gateway['path'];
                                $data['payment'][$row->id]['status'] = $gateway['status'];
                            }
                        }
                    }

                    // Set return
                    $return['status']           = 1;
                    $return['data']             = $data;
                    $return['data']['shipping'] = $_SESSION['checkout']['shipping'];
                    $return['delivery']         = $delivery['title'];
                    $return['payment']          = ($config['order_method'] == 'offline') ? __('Offline') : '';
                }
                break;

            case 'payment':
                if ($id) {
                    // Set delivery
                    $_SESSION['checkout']['payment']       = $id;
                    $_SESSION['checkout']['payment_title'] = $id;

                    // Set return
                    $data = [
                        'location' => $_SESSION['checkout']['location_title'],
                        'delivery' => $_SESSION['checkout']['delivery_title'],
                        'payment'  => $_SESSION['checkout']['payment_title'],
                    ];

                    // Set return
                    $return['status'] = 1;
                    $return['data']   = $data;
                }
                break;
        }

        // return
        return $return;
    }

    public function deleteAction()
    {
        $addressId = $this->params('address');
        $address   = Pi::model('customer_address', 'order')->find($addressId);
        $uid       = Pi::user()->getId();
        if ($address->uid == $uid) {
            $address->delete();
            return ['status' => 1];
        }
        return ['status' => 0];
    }

    public function promocodeAction()
    {
        if ($this->request->isPost()) {
            $cart              = Pi::api('order', 'order')->getOrderInfo();
            $option            = [];
            $formPromoCheckout = new PromoCheckoutForm('promoCheckout', $option);
            $formPromoCheckout->setInputFilter(new PromoCheckoutFilter($option));
            $data = $this->request->getPost();
            $formPromoCheckout->setData($data);
            if ($formPromoCheckout->isValid()) {
                $values    = $formPromoCheckout->getData();
                $promocode = Pi::api('promocode', 'order')->get($values['code']);
                if ($promocode) {
                    $authorizedModules = json_decode($promocode['module']);
                    if (time() < $promocode->time_start || time() > $promocode->time_end) {
                        // Code dépassé
                        $msgPromoCode = [
                            'type'    => 'info',
                            'message' => __('This code has expired.'),
                        ];
                    } else {
                        if (!in_array($cart['module_name'], $authorizedModules)) {
                            // mauvais module
                            $msgPromoCode = [
                                'type'    => 'info',
                                'message' => __('This code cannot be applied on this product.'),
                            ];
                        } else {
                            // promo existantes
                            $canUpdate = true;
                            foreach ($cart['product'] as &$product) {
                                if ($product['discount'] > 0) {
                                    $canUpdate = false;
                                    if ($product['discount'] < $promocode->promo) {
                                        $product['discount_price'] = $product['product_price'] * $promocode->promo / 100;
                                        $product['discount']       = $promocode->promo;
                                        $product['vat_price']      = ($product['product_price'] - $product['discount_price']) * $product['vat'] / 100;
                                        $product['promotion_code'] = $promocode->code;
                                        Pi::api('order', 'order')->setOrderInfo($cart);
                                    }

                                    $msgPromoCode = [
                                        'type'    => 'success',
                                        'message' => __(
                                            "You are trying to use a promo code on a product that already has a discount. We have automatically applied the most advantageous discount for you (it is not possible to cumulate the discounts)"
                                        ),
                                    ];
                                }
                            }

                            // MAJ $cart
                            if ($canUpdate) {
                                foreach ($cart['product'] as &$product) {
                                    $product['discount']       = $promocode->promo;
                                    $product['discount_price'] = $product['product_price'] * $promocode->promo / 100;
                                    $product['vat_price']      = ($product['product_price'] - $product['discount_price']) * $product['vat'] / 100;
                                    $product['promotion_code'] = $promocode->code;
                                }
                                Pi::api('order', 'order')->setOrderInfo($cart);

                                $msgPromoCode = [
                                    'type'    => 'success',
                                    'message' => __("Promo code accepted"),
                                ];
                            }
                        }
                    }
                } else {
                    // Code inexistant
                    $msgPromoCode = [
                        'type'    => 'info',
                        'message' => __("This code doesn't exist"),
                    ];
                }
            }
        }

        // Set price
        $price = $this->updatePrice($cart);

        $totalDiscount = 0;
        foreach ($cart['product'] as &$product) {
            $product['discount_price_view'] = Pi::api('api', 'order')->viewPrice($product['discount_price']);
            $totalDiscount                  += Pi::api('api', 'order')->viewPrice($product['discount_price']);
        }

        $price['total_price_view']     = Pi::api('api', 'order')->viewPrice($price['product'] - $totalDiscount);
        $price['vat_view']             = Pi::api('api', 'order')->viewPrice($price['vat']);
        $price['total_price_ttc_view'] = Pi::api('api', 'order')->viewPrice($price['product'] - $totalDiscount + $price['vat']);

        return ['msgPromoCode' => $msgPromoCode, 'cart' => $cart, 'price' => $price];
    }

    private function validValues($values, $cart, $uid, $config)
    {
        // Set value
        $values['uid']             = $uid;
        $values['ip']              = Pi::user()->getIp();
        $values['status_order']    = \Module\Order\Model\Order::STATUS_ORDER_VALIDATED;
        $values['status_delivery'] = 1;
        $values['time_create']     = time();
        $values['time_order']      = time();

        // Set type_commodity values
        if (isset($cart['type_commodity']) && in_array($cart['type_commodity'], ['product', 'service', 'booking'])) {
            $values['type_commodity'] = $cart['type_commodity'];
        }

        // Set type_payment values
        if (isset($cart['type_payment']) && in_array($cart['type_payment'], ['free', 'onetime', 'recurring', 'installment'])) {
            $values['type_payment'] = $cart['type_payment'];
        }

        // Set plan values
        if (isset($cart['plan']) && !empty($cart['plan'])) {
            $values['plan'] = $cart['plan'];
        }

        // Set module_name values
        if (isset($cart['module_name']) && !empty($cart['module_name'])) {
            $values['module'] = $cart['module_name'];
        }

        // Set module_table values
        if (isset($cart['module_table']) && !empty($cart['module_table'])) {
            $values['product_type'] = $cart['module_table'];
        } else {
            if ($cart['module_name'] == 'guide') {
                if ($values['type_commodity'] == 'booking') {
                    $values['product_type'] = 'booking';
                } else {
                    $values['product_type'] = 'package';
                }
            } else {
                if ($cart['module_name'] == 'event') {
                    $values['product_type'] = 'event';
                } elseif ($cart['module_name'] == 'shop') {
                    $values['product_type'] = 'product';
                }
            }
        }

        // Set module_item values
        if (isset($cart['module_item']) && !empty($cart['module_item'])) {
            $values['module_item'] = $cart['module_item'];
        }

        // Set can_pay values
        if (isset($cart['can_pay']) && !empty($cart['can_pay'])) {
            $values['can_pay'] = $cart['can_pay'];
        }

        // Set promotion_type values
        if (isset($cart['promotion_type']) && !empty($cart['promotion_type'])) {
            $values['promotion_type'] = $cart['promotion_type'];
        }

        // Set promotion_value values
        if (isset($cart['promotion_value']) && !empty($cart['promotion_value'])) {
            $values['promotion_value'] = $cart['promotion_value'];
        }

        // Set price values
        $values['discount_price'] = isset($cart['total_discount']) ? $cart['total_discount'] : 0;
        $values['shipping_price'] = isset($cart['total_shipping']) ? $cart['total_shipping'] : 0;
        $values['packing_price']  = isset($cart['total_packing']) ? $cart['total_packing'] : 0;
        $values['setup_price']    = isset($cart['total_setup']) ? $cart['total_setup'] : 0;
        $values['vat_price']      = isset($cart['total_vat']) ? $cart['total_vat'] : 0;
        $values['extra']          = isset($cart['extra']) ? json_encode($cart['extra']) : null;
        $values['product_price']  = 0;
        $values['total_price']    = 0;
        $values['unconsumed']     = 0;

        // Check order values
        if (!empty($cart['product'])) {
            foreach ($cart['product'] as $product) {
                $unconsumedPrice = json_decode($product['extra'], true)['unconsumedPrice'];

                // Set other price
                $values['product_price']   = ($product['product_price'] * $product['number']) + $values['product_price'];
                $values['discount_price']  = ($product['discount_price'] * $product['number']) + $values['discount_price'];
                $values['shipping_price']  = ($product['shipping_price'] * $product['number']) + $values['shipping_price'];
                $values['packing_price']   = ($product['packing_price'] * $product['number']) + $values['packing_price'];
                $values['setup_price']     = isset($product['setup_price']) ? ($product['setup_price'] * $product['number']) + $values['setup_price']
                    : $values['setup_price'];
                $values['vat_price']       = ($product['vat_price'] * $product['number']) + $values['vat_price'];
                $values['unconsumedPrice'] = $unconsumedPrice + isset($values['unconsumedPrice']) ? $values['unconsumedPrice'] : 0;
            }
        }

        // Check delivery and location for get price
        if (isset($values['location'])
            && intval($values['location']) > 0
            && isset($values['delivery'])
            && intval($values['delivery']) > 0
        ) {
            $shippingPrice            = Pi::api('delivery', 'order')->getPrice($values['location'], $values['delivery']);
            $values['shipping_price'] = $values['shipping_price'] + $shippingPrice;
        }

        // Set additional price
        if ($values['type_commodity'] == 'product' && $config['order_additional_price_product'] > 0) {
            $values['shipping_price'] = $values['shipping_price'] + $config['order_additional_price_product'];
        } elseif (in_array($values['type_commodity'], ['service', 'booking']) && $config['order_additional_price_service'] > 0) {
            $values['setup_price'] = $values['setup_price'] + $config['order_additional_price_service'];
        }

        // Set total
        $values['total_price'] = ((
                $values['product_price'] +
                $values['shipping_price'] +
                $values['packing_price'] +
                $values['setup_price'] +
                $values['vat_price']
            ) - $values['discount_price'] - $values['unconsumedPrice']);

        return $values;
    }

    private function updatePrice($cart)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set price
        $price               = [];
        $price['discount']   = isset($cart['total_discount']) ? $cart['total_discount'] : 0;
        $price['shipping']   = isset($cart['total_shipping']) ? $cart['total_shipping'] : 0;
        $price['packing']    = isset($cart['total_packing']) ? $cart['total_packing'] : 0;
        $price['setup']      = isset($cart['total_setup']) ? $cart['total_setup'] : 0;
        $price['vat']        = isset($cart['total_vat']) ? $cart['total_vat'] : 0;
        $price['product']    = 0;
        $price['total']      = 0;
        $price['unconsumed'] = 0;
        $unconsumedPrice     = 0;

        // Set price by product
        if (isset($cart['product']) && count($cart['product'])) {
            foreach ($cart['product'] as $product) {
                // Check setup price
                $extra           = json_decode($product['extra'], true);
                $unconsumedPrice = isset($extra['unconsumedPrice']) ? $extra['unconsumedPrice'] : null;

                // Check setup price
                $product['setup_price'] = isset($product['setup_price']) ? $product['setup_price'] : 0;

                // Set price
                $price['product']    += round((float)$product['product_price'] * (float)$product['number'] * 100) / 100;
                $price['discount']   += round((float)$product['discount_price'] * (float)$product['number'] * 100) / 100;
                $price['shipping']   += round((float)$product['shipping_price'] * (float)$product['number'] * 100) / 100;
                $price['setup']      += round((float)$product['setup_price'] * (float)$product['number'] * 100) / 100;
                $price['packing']    += round((float)$product['packing_price'] * (float)$product['number'] * 100) / 100;
                $price['vat']        += round((float)$product['vat_price'] * 100) / 100;
                $price['unconsumed'] += $unconsumedPrice;
            }
        }

        // Set additional price
        if (isset($cart['type_commodity'])) {
            if ($cart['type_commodity'] == 'product' && $config['order_additional_price_product'] > 0) {
                $price['shipping'] = $price['shipping'] + $config['order_additional_price_product'];
            } elseif (in_array($cart['type_commodity'], ['service', 'booking']) && $config['order_additional_price_service'] > 0) {
                $price['setup'] = $price['setup'] + $config['order_additional_price_service'];
            }
        }

        // Set total
        $price['total'] = ((
                $price['product'] +
                $price['shipping'] +
                $price['packing'] +
                $price['setup'] +
                $price['vat']
            ) - $price['discount'] - $unconsumedPrice);

        return $price;
    }

    private function saveOrder($values, $addressDelivery, $addressInvoicing, $cart, $config, $uid)
    {
        // Set user id to session
        $_SESSION['order']['uid'] = $uid;

        // valid values
        $values = $this->validValues($values, $cart, $uid, $config);

        // Check gateway
        $values['default_gateway'] = is_array($values['default_gateway']) ? array_shift($values['default_gateway']) : $values['default_gateway'];

        // Set gateway to session
        $_SESSION['order']['gateway'] = $values['default_gateway'];

        // Get gateway
        if ($values['default_gateway'] != 'Offline') {
            $gateway = Pi::api('gateway', 'order')->getGateway($values['default_gateway']);
            if ($gateway->getType() == AbstractGateway::TYPE_REST) {
                $_SESSION['order']['redirect'] = $cart['redirect'];
            }

            // Get gateway
            $gateway        = Pi::api('gateway', 'order')->getGatewayInfo($values['default_gateway']);
            $gatewayOptions = json_decode($gateway['option'], true);
        } else {
            $gateway        = [
                'name' => __('Offline'),
                'path' => 'Offline',
            ];
            $gatewayOptions = [
                'onemail' => false,
            ];
            $config['order_payment'] = 'invoice';
        }

        // Save order
        if (isset($_SESSION['order']['id'])) {
            $order = $this->getModel('order')->find($_SESSION['order']['id']);
        } else {
            $order = $this->getModel('order')->createRow();

            // generate order code
            $values['code'] = Pi::api('order', 'order')->generateCode();
        }
        $order->assign($values);
        $order->save();

        // Set order id to session
        $_SESSION['order']['id'] = $order['id'];

        // Save order address
        $orderAddress = $this->getModel('order_address')->createRow();
        unset($addressDelivery['id']);
        $addressDelivery['type']  = 'DELIVERY';
        $addressDelivery['order'] = $order['id'];
        $orderAddress->assign($addressDelivery);
        $orderAddress->save();

        // Save order address
        $orderAddress = $this->getModel('order_address')->createRow();
        unset($addressInvoicing['id']);
        $addressInvoicing['type']  = 'INVOICING';
        $addressInvoicing['order'] = $order['id'];
        $orderAddress->assign($addressInvoicing);
        $orderAddress->save();

        // Log term and condition acceptation
        if (Pi::service('module')->isActive('user')) {
            $condition = Pi::api('condition', 'user')->getLastEligibleCondition();
            if ($condition && isset($values['order_term']) && $values['order_term'] == 1) {
                $log = [
                    'uid'    => $uid,
                    'data'   => $condition->version,
                    'action' => 'accept_conditions_checkout',
                ];

                Pi::api('log', 'user')->add(null, null, $log);
            }
        }

        // Check order save
        if (isset($order->id) && intval($order->id) > 0) {

            // Save order detail
            if (!empty($cart['product'])) {
                $this->getModel('detail')->delete(['order' => $_SESSION['order']['id']]);
                foreach ($cart['product'] as $product) {
                    $unconsumedPrice           = json_decode($product['extra'], true)['unconsumedPrice'];
                    $product['discount_price'] += $unconsumedPrice;

                    // Save detail
                    $detail                 = $this->getModel('detail')->createRow();
                    $detail->order          = $order->id;
                    $detail->module         = $values['module'];
                    $detail->product_type   = $values['product_type'];
                    $detail->product        = $product['product'];
                    $detail->discount_price = (isset($product['discount_price']) && !empty($product['discount_price'])) ? $product['discount_price'] : 0;
                    $detail->shipping_price = (isset($product['shipping_price']) && !empty($product['shipping_price'])) ? $product['shipping_price'] : 0;
                    $detail->setup_price    = (isset($product['setup_price']) && !empty($product['setup_price'])) ? $product['setup_price'] : 0;
                    $detail->packing_price  = (isset($product['packing_price']) && !empty($product['packing_price'])) ? $product['packing_price'] : 0;
                    $detail->vat_price      = (isset($product['vat_price']) && !empty($product['vat_price'])) ? $product['vat_price'] : 0;
                    $detail->time_create    = time();
                    $detail->number         = $product['number'];
                    $detail->time_start     = isset($product['time_start']) ? $product['time_start'] : 0;
                    $detail->time_end       = isset($product['time_end']) ? $product['time_end'] : 0;

                    // Set price
                    $formatter = Pi::service('i18n')->getNumberFormatter();
                    $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
                    $formatter->setSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, '.');

                    $detail->product_price = number_format($product['product_price'], 2, '.', '');
                    $detail->vat_price     = $detail->vat_price - ($detail->product_price - $product['product_price']);

                    $extra = [];
                    if ($product['extra']) {
                        $extra = json::decode($product['extra'], true);
                    }

                    $detail->extra = json::encode($extra);
                    if (array_key_exists('unconsumedPrice', $extra)) {
                        unset($extra['unconsumedPrice']);
                    }

                    if (isset($product['promotion_code']) && !empty($product['promotion_code'])) {
                        $detail->promotion_code = $product['promotion_code'];
                    }

                    $detail->save();
                }

                // Save general detail
                // ToDo : fix this part for custom service price
                /* $detail                 = $this->getModel('detail')->createRow();
                $detail->order          = $order->id;
                $detail->module         = 'order';
                $detail->product_type   = 'service';
                $detail->product        = 0;
                $detail->discount_price = (isset($values['discount_price']) && !empty($values['discount_price'])) ? $values['discount_price'] : 0;
                $detail->shipping_price = (isset($values['shipping_price']) && !empty($values['shipping_price'])) ? $values['shipping_price'] : 0;
                $detail->setup_price    = (isset($values['setup_price']) && !empty($values['setup_price'])) ? $values['setup_price'] : 0;
                $detail->packing_price  = (isset($values['packing_price']) && !empty($values['packing_price'])) ? $values['packing_price'] : 0;
                $detail->vat_price      = (isset($values['vat_price']) && !empty($values['vat_price'])) ? $values['vat_price'] : 0;
                $detail->product_price  = 0;
                $detail->time_create    = time();
                $detail->number         = 1;
                $detail->time_start     = 0;
                $detail->time_end       = 0;
                $detail->save(); */

                // Manage invoice
                $invoices = Pi::api('invoice', 'order')->getInvoiceFromOrder($order->id);
                if (!count($invoices)) {
                    $result = Pi::api('invoice', 'order')->createInvoice($order->id, Pi::user()->getId());
                } else {
                    $result = reset($invoices);
                    Pi::api('installment', 'order')->removeInstallments($result['id']);
                }
                $randomId    = $result['random_id'];
                $composition = Pi::api('order', $cart['module_name'])->getInstallmentComposition($cart, true);
                $dates       = Pi::api('order', $cart['module_name'])->getInstallmentDueDate($cart, $composition);
                $invoice     = Pi::api('invoice', 'order')->updateInvoice($randomId, $gateway['path'], $composition, $dates, false, $values['type_payment']);
            }

            // Update user information
            if ($config['order_update_user'] && isset($values['update_user']) && $values['update_user']) {
                Pi::api('user', 'order')->updateUserInformation($values);
            }

            // Add user credit
            if (isset($cart['credit'])) {
                $cart['credit']['module'] = $values['module'];
                Pi::api('credit', 'order')->addHistory($cart['credit'], $order->id);
            }

            // Save order entity again for triggering observers
            $order->save();

            // Send notification
            Pi::api('notification', 'order')->addOrder($order->toArray(), $addressInvoicing, $gatewayOptions['onemail']);

            // Set jump url
            if ($config['order_payment'] == 'payment') {
                $url = Pi::url(
                    Pi::service('url')->assemble(
                        'order',
                        [
                            'module'     => $this->getModule(),
                            'controller' => 'payment',
                            'action'     => 'index',
                            'id'         => $order->id,
                        ]
                    )
                );
            } else {
                $url = Pi::url(
                    Pi::service('url')->assemble(
                        'order',
                        [
                            'module'     => $this->getModule(),
                            'controller' => 'detail',
                            'action'     => 'index',
                            'id'         => $order->id,
                        ]
                    )
                );
            }

            // Jump to url
            $this->jump($url, __('Order information saved successfully, you can now finalize payment !'), 'success');
        } else {
            $error = [
                'values' => $values,
                'cart'   => $cart,
            ];
            $this->view()->assign('error', $error);
        }
    }

    private function createUserAccount($values)
    {
        // Set time
        $values['time_create'] = time();

        // Check user module active
        if (Pi::service('module')->isActive('user')
            && !Pi::service('authentication')->hasIdentity()
            && isset($_SESSION['session_order'])
            && !empty($_SESSION['session_order'])
        ) {

            // Check email force set on register form
            if (!isset($values['email']) || empty($values['email'])) {
                $result['message'] = __('User information was not completed and user account was not saved.');
                return $result;
            }

            // Set email as identity if not set on register form
            if (!isset($values['identity']) || empty($values['identity'])) {
                $values['identity'] = $values['email'];
            }

            // Set name if not set on register form
            if (!isset($values['name']) || empty($values['name'])) {
                if (isset($values['first_name']) || isset($values['last_name'])) {
                    $values['name'] = $values['first_name'] . ' ' . $values['last_name'];
                } else {
                    $values['name'] = $values['identity'];
                }
            }

            // Set values
            $values['last_modified'] = time();
            $values['ip_register']   = Pi::user()->getIp();

            // Add user
            $values['uid'] = Pi::api('user', 'user')->addUser($values);
            if (!$values['uid'] || !is_int($values['uid'])) {
                $url = Pi::url(Pi::service('user')->getUrl('register', []));
                $this->jump($url, __('User account was not saved.'), 'error');
            }

            // Set user role
            Pi::api('user', 'user')->setRole($values['uid'], 'member');

            // Active user
            $status = Pi::api('user', 'user')->activateUser($values['uid']);
            if ($status) {
                // Target activate user event
                Pi::service('event')->trigger('user_activate', $values['uid']);
            }

            return $values;
        }

        return [];
    }
}

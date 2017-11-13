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

namespace Module\Order\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
//use Pi\Authentication\Result;
use Module\Order\Form\AddressForm;
use Module\Order\Form\AddressFilter;
use Module\Order\Form\OrderForm;
use Module\Order\Form\OrderFilter;
use Module\Order\Form\OrderSimpleForm;
use Module\Order\Form\OrderSimpleFilter;
use Module\System\Form\LoginForm;
use Module\System\Form\LoginFilter;
use Zend\Json\Json;

class CheckoutController extends IndexController
{
   private function order($values, $customer, $cart, $config, $uid)
   {
       
        $values['uid'] = $uid;
        $values['ip'] = Pi::user()->getIp();
        $values['status_order'] = 1;
        $values['status_payment'] = 1;
        $values['status_delivery'] = 1;
        $values['time_create'] = time();
        
        unset($customer['id']);
        unset($customer['uid']);
        unset($customer['ip']);
        unset($customer['id_number']);
        unset($customer['user_note']);
        unset($customer['time_create']);
        unset($customer['time_update']);
        unset($customer['status']);
        unset($customer['time_create_view']);
        unset($customer['time_update_view']);
        $values = $values + $customer;
        
        // Set type_payment values
        if (isset($cart['type_commodity']) && in_array($cart['type_commodity'], array('product', 'service'))) {
            $values['type_commodity'] = $cart['type_commodity'];
        }
        // Set plan values
        if (isset($cart['plan']) && !empty($cart['plan'])) {
            $values['plan'] = $cart['plan'];
        }
        // Set module_name values
        if (isset($cart['module_name']) && !empty($cart['module_name'])) {
            $values['module_name'] = $cart['module_name'];
        }
        // Set module_table values
        if (isset($cart['module_table']) && !empty($cart['module_table'])) {
            $values['module_table'] = $cart['module_table'];
        }
        // Set module_item values
        if (isset($cart['module_item']) && !empty($cart['module_item'])) {
            $values['module_item'] = $cart['module_item'];
        }
        // Set can_pay values
        if (isset($cart['can_pay']) && !empty($cart['can_pay'])) {
            $values['can_pay'] = $cart['can_pay'];
        }
        // Check gateway
        if (is_array($values['gateway'])) {
            $values['gateway'] = $values['gateway'][0]; 
        }
        $gateway = Pi::api('gateway', 'order')->getGatewayInfo($values['gateway']);
        $gatewayOptions = json_decode($gateway['option'], true);
            
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
        $values['packing_price'] = isset($cart['total_packing']) ? $cart['total_packing'] : 0;
        $values['setup_price'] = isset($cart['total_setup']) ? $cart['total_setup'] : 0;
        $values['vat_price'] = isset($cart['total_vat']) ? $cart['total_vat'] : 0;
        $values['product_price'] = 0;
        $values['total_price'] = 0;

        // Check order values
        if (!empty($cart['product'])) {
            foreach ($cart['product'] as $product) {
                // Set other price
                $values['product_price'] = ($product['product_price'] * $product['number']) + $values['product_price'];
                $values['discount_price'] = ($product['discount_price'] * $product['number']) + $values['discount_price'];
                $values['shipping_price'] = ($product['shipping_price'] * $product['number']) + $values['shipping_price'];
                $values['packing_price'] = ($product['packing_price'] * $product['number']) + $values['packing_price'];
                $values['setup_price'] = ($product['setup_price'] * $product['number']) + $values['setup_price'];
                $values['vat_price'] = ($product['vat_price'] * $product['number']) + $values['vat_price'];
            }
        }

        // Check delivery and location for get price
        if (isset($values['location'])
            && intval($values['location']) > 0
            && isset($values['delivery'])
            && intval($values['delivery']) > 0
        ) {
            $shippingPrice = Pi::api('delivery', 'order')->getPrice($values['location'], $values['delivery']);
            $values['shipping_price'] = $values['shipping_price'] + $shippingPrice;
        }

        // Set additional price
        if ($values['type_commodity'] == 'product' && $config['order_additional_price_product'] > 0) {
            $values['shipping_price'] = $values['shipping_price'] + $config['order_additional_price_product'];
        } elseif ($values['type_commodity'] == 'service' && $config['order_additional_price_service'] >0 ) {
            $values['setup_price'] = $values['setup_price'] + $config['order_additional_price_service'];
        }

        // Set total
        $values['total_price'] = (($values['product_price'] +
                $values['shipping_price'] +
                $values['packing_price'] +
                $values['setup_price'] +
                $values['vat_price']
            ) - $values['discount_price']);

        

        // Save values to order
        $order = $this->getModel('order')->createRow();
        $order->assign($values);
        $order->save();

        // Log term and condition acceptation
        if (Pi::service('module')->isActive('user')){
            $condition = Pi::api('condition', 'user')->getLastEligibleCondition();

            if($condition && isset($values['order_term']) && $values['order_term'] == 1){
                $log = array(
                    'uid' => $uid,
                    'data' => $condition->version,
                    'action' => 'accept_conditions_checkout',
                );

                Pi::api('log', 'user')->add(null, null, $log);
            }
        }
        
        // Check order save
        if (isset($order->id) && intval($order->id) > 0) {
            // Set order ID
            $code = Pi::api('order', 'order')->generatCode($order->id);
            $this->getModel('order')->update(
                array('code' => $code),
                array('id' => $order->id)
            );
            // Save order basket
            if (!empty($cart['product'])) {
                foreach ($cart['product'] as $product) {
                    $price = $product['product_price'];
                    $total = (($product['product_price'] +
                                $product['shipping_price'] +
                                $product['packing_price'] +
                                $product['setup_price'] +
                                $product['vat_price']
                            ) - $product['discount_price']) * $product['number'];
                    
                    // Save basket
                    $basket = $this->getModel('basket')->createRow();
                    $basket->order = $order->id;
                    $basket->product = $product['product'];
                    $basket->discount_price = isset($product['discount_price']) ? $product['discount_price'] : 0;
                    $basket->shipping_price = isset($product['shipping_price']) ? $product['shipping_price'] : 0;
                    $basket->setup_price = isset($product['setup_price']) ? $product['setup_price'] : 0;
                    $basket->packing_price = isset($product['packing_price']) ? $product['packing_price'] : 0;
                    $basket->vat_price = isset($product['vat_price']) ? $product['vat_price'] : 0;
                    // Set price
                    if ($order->type_payment == 'installment') {
                        $basket->product_price = Pi::api('installment', 'order')->setTotlaPriceForInvoice($price, $order->plan);
                        $basket->total_price = Pi::api('installment', 'order')->setTotlaPriceForInvoice($total, $order->plan);
                    } else {
                        $basket->product_price = $price;
                        $basket->total_price = $total;
                    }
                    $basket->number = $product['number'];
                    // Set installment to extra
                    if ($order->type_payment == 'installment') {
                        $extra = array();
                        $extra['product'] = json::decode($product['extra'], true);
                        $extra['installment'] = Pi::api('installment', 'order')->setPriceForProduct($total, $order->plan);
                        $extra['installment_main_price'] = $price;
                        $extra['installment_main_total'] = $total;
                        $extra['installment_new_price'] = Pi::api('installment', 'order')->setTotlaPriceForInvoice($price, $order->plan);
                        $extra['installment_new_total'] = Pi::api('installment', 'order')->setTotlaPriceForInvoice($total, $order->plan);
                        $basket->extra = json::encode($extra);
                    } else {
                        $extra = array();
                        if($product['extra']){
                            $extra['product'] = json::decode($product['extra'], true);
                        }

                        if (isset($extra['product']['view_type'])) {
                            $extra['view_type'] = $extra['product']['view_type'];
                            unset($extra['product']['view_type']);
                        }

                        if (isset($extra['product']['view_template'])) {
                            $extra['view_template'] = $extra['product']['view_template'];
                            unset($extra['product']['view_template']);
                        }

                        if (isset($extra['product']['getDetail'])) {
                            $extra['getDetail'] = $extra['product']['getDetail'];
                            unset($extra['product']['getDetail']);
                        }

                        $basket->extra = json::encode($extra);
                    }
                    $basket->save();
                }
            }
            // Update user information
            if ($config['order_update_user'] && isset($values['update_user']) && $values['update_user']) {
                Pi::api('user', 'order')->updateUserInformation($values);
            }
            // Set invoice
            $result = Pi::api('invoice', 'order')->createInvoice($order->id, $uid);
            // Add user credit
            if (isset($cart['credit'])) {
                $cart['credit']['module'] = $order->module_name;
                Pi::api('credit', 'order')->addHistory($cart['credit'], $order->id);
            }
            // unset order
            Pi::api('order', 'order')->unsetOrderInfo();
            // Send notification
            if (!$gatewayOptions['onemail']) {
                Pi::api('notification', 'order')->addOrder($order->toArray());
            }
            // Go to payment
            if ($result['status'] == 0) {
                $url = array('', 'controller' => 'index', 'action' => 'index');
                $this->jump($url, $result['message'], 'error');
            } else {
                if ($config['order_payment'] == 'payment') {
                    $url = $result['pay_url'];
                } else {
                    $url = $result['order_url'];
                }
                $this->jump($url, $result['message'], 'success');
            }
        } else {
            $error = array(
                'values' => $values,
                'cart' => $cart,
                //'customers' => $customers,
                //'user' => $user,
            );
            $this->view()->assign('error', $error);
        }
        
   }
    public function indexAction()
    {

        // Set check
        $check = false;
        $editAddress = false;
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set cart
        $cart = Pi::api('order', 'order')->getOrderInfo();
        if (empty($cart)) {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('Your cart is empty.'), 'error');
        }
        
        // Check order is active or inactive
        if (!$config['order_active']) {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }
        
        // Check user
        if (!Pi::service('authentication')->hasIdentity()) {
            if (Pi::service('module')->isActive('user')
                && isset($_SESSION['session_order'])
                && !empty($_SESSION['session_order'])
            ) {
                // Load language
                Pi::service('i18n')->load(array('module/system', 'default'));
                Pi::service('i18n')->load(array('module/user', 'default'));
                // Set login form
                $formLogin = new LoginForm('login');
                $formLogin->setAttribute(
                    'action',
                    $this->url('user', array('module' => 'user', 'controller' => 'login', 'action' => 'process'))
                );
                $formLogin->setData(array('redirect' => $this->url('', array('controller' => 'checkout', 'action' => 'index'))));
                $this->view()->assign('formLogin', $formLogin);
            } else {
                $this->jump(Pi::url(), __('Your cart is empty.'), 'error');
            }
        }
            
        // Get customer
        $customers = Pi::api('customer', 'order')->findCustomer();
        // Sety form option
        $option = array(
            'type_commodity' => $cart['type_commodity'],
            'customers' => $customers,
        );
        
        $formAddress = new AddressForm();
        $formAddress->setInputFilter(new AddressFilter($option));
        
        $formOrderSimple = new OrderSimpleForm('order', $option);
        $formOrderSimple->setInputFilter(new OrderSimpleFilter($option));
            
        $formOrder = new OrderForm('order', $option);
        $formOrder->setInputFilter(new OrderFilter($option));

        // Check post
        $check = count($customers) == 0 ? true : false;
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            
            if (isset($data['submit_address'])) {
                $formAddress->setData($data);
                if ($formAddress->isValid()) {
                   
                    // Check user informations
                    $uid = Pi::user()->getId();
                    $user = Pi::api('user', 'order')->getUserInformation();
                    
                    $values = $formAddress->getData();
                    $values['time_create'] = time();
                    
                    $values['uid'] = $uid;
                    if ($values['customer_id'] == 0) {
                        Pi::api('customer', 'order')->addCustomer($values);
                    } else {
                        Pi::api('customer', 'order')->updateCustomer($values);
                    }
                    $url = $this->url('', array('controller' => 'checkout', 'action' => 'index'));
                    $this->jump($url);
                    
                } else {
                    $check = true;
                }
            } else if (isset($data['submit_order_simple'])) {
                $formOrderSimple->setData($data);
                if ($formOrderSimple->isValid()) {
                    
                    $uid = Pi::user()->getId();
                    $user = Pi::api('user', 'order')->getUserInformation();
                             
                     // Set values
                    $values = $formOrderSimple->getData();
                    
                    $customers = Pi::api('customer', 'order')->findCustomer($uid);
                    $customer = $customers[$values['customer_id']];
                   
                    
                    $this->order($values, $customer, $cart, $config, $uid);                  
                } 
            } else {
                $formOrder->setData($data);
                if ($formOrder->isValid()) {
                    $values = $formOrder->getData();
                    $values['time_create'] = time();
                     /*
                     * Register user codes from user module register controller
                     */
                    if (Pi::service('module')->isActive('user')
                        && !Pi::service('authentication')->hasIdentity()
                        && isset($_SESSION['session_order'])
                        && !empty($_SESSION['session_order'])
                    ) {
                        
                        /*
                         * Register part
                         */
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
                        $uid = Pi::api('user', 'user')->addUser($values);
                        if (!$uid || !is_int($uid)) {
                            $url = Pi::url(Pi::service('user')->getUrl('register', array()));
                            $this->jump($url, __('User account was not saved.'), 'error');
                        }
                        // Set user role
                        Pi::api('user', 'user')->setRole($uid, 'member');
    
                        /*
                         * Active user
                         */
                        $status = Pi::api('user', 'user')->activateUser($uid);
                        if ($status) {
                            // Target activate user event
                            Pi::service('event')->trigger('user_activate', $uid);
                        }
    
                        /*
                         * Get user information
                         */
                        // Check user informations
                        $user = Pi::api('user', 'order')->getUserInformation($uid);
                        $values['uid'] = $uid;
                        if ($values['customer_id'] == 0) {
                            Pi::api('customer', 'order')->addCustomer($values);
                        } else {
                            Pi::api('customer', 'order')->updateCustomer($values);
                        }
                        $customers = Pi::api('customer', 'order')->findCustomer($uid);
                        $customer = current($customers);
                        
                        
                        $this->order($values, $customer, $cart, $config, $uid);    
                    
                    } 
                }
            }
        } else {
            $customer = $this->params('customer');
            if ($customer) {
                $formAddress = new AddressForm($customer);
                $formAddress->setInputFilter(new AddressFilter($option));
                $values = $customers[$customer];
                $values['customer_id'] = $customer;
                $formAddress->setData($values);
                $check = true;
                $editAddress = true;
            }
        }

        // Set new form
        $user = Pi::api('user', 'order')->getUserInformation();
        $user['customer_id'] = 0;
        $forms = array();
        
        // Set customer forms
        if (!empty($customers)) {
            foreach ($customers as $customer) {
                $key = sprintf('customer-%s', $customer['id']);
                $option['location'] = $customer['location'];
                unset($customer['delivery']);
                unset($customer['user_note']);
               
            }
        }
        if (Pi::service('authentication')->hasIdentity()) {
            $forms['order'] = $formOrderSimple;
            $forms['new'] = $formAddress;
        } else {
            $forms['new'] = $formOrder;
        }

        // Set price
        $price['discount'] = isset($cart['total_discount']) ? $cart['total_discount'] : 0;
        $price['shipping'] = isset($cart['total_shipping']) ? $cart['total_shipping'] : 0;
        $price['packing'] = isset($cart['total_packing']) ? $cart['total_packing'] : 0;
        $price['setup'] = isset($cart['total_setup']) ? $cart['total_setup'] : 0;
        $price['vat'] = isset($cart['total_vat']) ? $cart['total_vat'] : 0;
        $price['product'] = 0;
        $price['total'] = 0;
        foreach ($cart['product'] as $product) {
            // Check setup price
            $product['setup_price'] = isset($product['setup_price']) ? $product['setup_price'] : 0;
            // Set price
            $price['product'] = ($product['product_price'] * $product['number']) + $price['product'];
            $price['discount'] = ($product['discount_price'] * $product['number']) + $price['discount'];
            $price['shipping'] = ($product['shipping_price'] * $product['number']) + $price['shipping'];
            $price['setup'] = ($product['setup_price'] * $product['number']) + $price['setup'];
            $price['packing'] = ($product['packing_price'] * $product['number']) + $price['packing'];
            $price['vat'] = $product['vat_price'] + $price['vat'];
        }

        // Set additional price
        if ($cart['type_commodity'] == 'product' && $config['order_additional_price_product'] > 0) {
            $price['shipping'] = $price['shipping'] + $config['order_additional_price_product'];
        } elseif ($cart['type_commodity'] == 'service' && $config['order_additional_price_service'] >0 ) {
            $price['setup'] = $price['setup'] + $config['order_additional_price_service'];
        }

        // Set total
        $price['total'] = (($price['product'] +
                $price['shipping'] +
                $price['packing'] +
                $price['setup'] +
                $price['vat']
            ) - $price['discount']);

        // Set plan
        if ($cart['type_payment'] == 'installment') {
            $user = Pi::api('user', 'order')->getUserInformation();
            $plan = Pi::api('installment', 'order')->setPriceForInvoice($price['total'], $cart['plan'], $user);
            $this->view()->assign('plan', $plan);
        }

        // Set products
        foreach ($cart['product'] as $product) {
            $cart['product'][$product['product']]['details'] = Pi::api('order', $cart['module_name'])->getProductDetails($product['product'], $product['extra']);
            $cart['product'][$product['product']]['product_price_view'] = Pi::api('api', 'order')->viewPrice($product['product_price']);
        }

        // Get credit
        /* if ($config['credit_active'] && Pi::user()->getId() > 0) {
            $credit = $this->getModel('credit')->find(Pi::user()->getId(), 'uid')->toArray();
            $credit['amount_view'] = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view'] = ($credit['time_update'] > 0) ? _date($credit['time_update']) : __('Never update');
            $this->view()->assign('credit', $credit);
        } */

        // Set view
        $this->view()->setTemplate('checkout');
        $this->view()->assign('forms', $forms);
        $this->view()->assign('cart', $cart);
        $this->view()->assign('price', $price);
        $this->view()->assign('config', $config);
        $this->view()->assign('customers', $customers);
        $this->view()->assign('check', $check);
        $this->view()->assign('editAddress', $editAddress);
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
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('Your cart is empty.'), 'error');
        }
        // Check order is active or inactive
        if (!$config['order_active']) {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
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
            $url = array('', 'controller' => 'checkout', 'action' => 'index');
            $this->jump($url, __('Your installment plan save, please complete your information and payment.'));

        } else {
            // Set user
            $user = Pi::api('user', 'order')->getUserInformation();
            // Set price
            $price = array();
            $price['product_price'] = 0;
            $price['discount_price'] = 0;
            $price['shipping_price'] = 0;
            $price['setup_price'] = 0;
            $price['packing_price'] = 0;
            $price['vat_price'] = 0;
            $price['total_price'] = 0;
            // Check order price
            if (!empty($cart['product'])) {
                foreach ($cart['product'] as $product) {
                    // Set price
                    $price['product_price'] = $product['product_price'] + $price['product_price'];
                    $price['discount_price'] = $product['discount_price'] + $price['discount_price'];
                    $price['shipping_price'] = $product['shipping_price'] + $price['shipping_price'];
                    $price['setup_price'] = $product['setup_price'] + $price['setup_price'];
                    $price['packing_price'] = $product['packing_price'] + $price['packing_price'];
                    $price['vat_price'] = $product['vat_price'] + $price['vat_price'];
                    // Set total
                    $total = (($product['product_price'] +
                                $product['shipping_price'] +
                                $product['packing_price'] +
                                $product['setup_price'] +
                                $product['vat_price']
                            ) - $product['discount_price']) * $product['number'];
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
        $id = $this->params('id');
        $process = $this->params('process');
        $module = $this->params('module');
        $return = array();
        $return['status'] = 0;
        $return['data'] = '';
        $data = array();
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
                    $location = $this->getModel('location')->find($id)->toArray();
                    $_SESSION['checkout']['location'] = $location['id'];
                    $_SESSION['checkout']['location_title'] = $location['title'];
                    // Get location
                    $where = array('location' => $id);
                    $select = $this->getModel('location_delivery')->select()->where($where);
                    $rowset = $this->getModel('location_delivery')->selectWith($select);
                    foreach ($rowset as $row) {
                        $delivery = $this->getModel('delivery')->find($row->delivery)->toArray();
                        if ($delivery['status']) {
                            $data[$row->id] = $row->toArray();
                            $data[$row->id]['title'] = $delivery['title'];
                            $data[$row->id]['status'] = $delivery['status'];
                        }
                    }
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = $data;
                    $return['location'] = $location['title'];
                }
                break;

            case 'delivery':
                if ($id) {
                    // Set delivery
                    $delivery = $this->getModel('delivery')->find($id)->toArray();
                    $_SESSION['checkout']['delivery'] = $delivery['id'];
                    $_SESSION['checkout']['delivery_title'] = $delivery['title'];
                    // Get location_delivery
                    $location = $_SESSION['checkout']['location'];
                    $where = array('location' => $location, 'delivery' => $id);
                    $select = $this->getModel('location_delivery')->select()->where($where)->limit(1);
                    $row = $this->getModel('location_delivery')->selectWith($select)->current();
                    // Set shipping price
                    $_SESSION['checkout']['shipping'] = $row->price;
                    // Get delivery_payment
                    $where = array('delivery' => $id);
                    $select = $this->getModel('delivery_gateway')->select()->where($where);
                    $rowset = $this->getModel('delivery_gateway')->selectWith($select);
                    foreach ($rowset as $row) {
                        if ($row->gateway == 'offline') {
                            $data['payment'][$row->id]['title'] = 'Offline';
                            $data['payment'][$row->id]['path'] = 'offline';
                            $data['payment'][$row->id]['status'] = 1;
                        } else {
                            $gateway = Pi::api('gateway', 'order')->getGatewayInfo($row->gateway);
                            if ($gateway['status']) {
                                $data['payment'][$row->id]['title'] = $gateway['title'];
                                $data['payment'][$row->id]['path'] = $gateway['path'];
                                $data['payment'][$row->id]['status'] = $gateway['status'];
                            }
                        }
                    }
                    // Set return
                    //$return['status'] = 1;
                    //$return['data'] = $data;
                    //$return['data']['shipping'] = $invoice['total']['shipping'];
                    //$return['data']['total'] = $invoice['total']['total_price'];
                    //$return['delivery'] = $delivery['title'];
                    //$return['payment'] = ($config['order_method'] == 'offline') ? __('Offline') : '';
                }
                break;

            case 'payment':
                if ($id) {
                    // Set delivery
                    $_SESSION['checkout']['payment'] = $id;
                    $_SESSION['checkout']['payment_title'] = $id;
                    // Set return
                    $data = array(
                        'location' => $_SESSION['checkout']['location_title'],
                        'delivery' => $_SESSION['checkout']['delivery_title'],
                        'payment' => $_SESSION['checkout']['payment_title'],
                    );
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = $data;
                }
                break;
        }
        // return
        return $return;
    }

    public function deleteAction()
    {
        $customerId = $this->params('customer');
        $customer = Pi::model('customer', 'order')->find($customerId);
        $uid = Pi::user()->getId();
        if ($customer->uid == $uid) {
            $customer->delete();
        }
        $url = array('', 'controller' => 'checkout', 'action' => 'index');
        $this->jump($url, __('Address deleted'));       
    }
    
}

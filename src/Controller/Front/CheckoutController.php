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
use Module\Order\Form\OrderForm;
use Module\Order\Form\OrderFilter;

class CheckoutController extends IndexController
{
    public function indexAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set cart
        $cart = $_SESSION['order'];
        if (empty($cart)) {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('Your cart is empty.'), 'error');
        }
        // Check order is active or inactive
        if ($config['order_method'] == 'inactive') {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }
        // Set order form
        $form = new OrderForm('order');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Set values
                $values['code'] = Pi::api('order', 'order')->generatCode();
                $values['uid'] = Pi::user()->getId();
                $values['ip'] = Pi::user()->getIp();
                $values['status_order'] = 1;
                $values['status_payment'] = 1;
                $values['status_delivery'] = 1;
                $values['time_create'] = time();
                // Set type values
                if (isset($cart['type']) && in_array($cart['type'], array('free','onetime','recurring','installment'))) {
                    $values['type'] = $cart['type'];
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
                // Set price values
                $values['product_price'] = 0;
                $values['discount_price'] = 0;
                $values['shipping_price'] = 0;
                $values['packing_price'] = 0;
                $values['vat_price'] = 0;
                // Check order values
                if (!empty($cart['product'])) {
                    foreach ($cart['product'] as $product) {
                        $values['product_price'] = $product['product_price'] + $values['product_price'];
                        $values['discount_price'] = $product['discount_price'] + $values['discount_price'];
                        $values['shipping_price'] = $product['shipping_price'] + $values['shipping_price'];
                        $values['packing_price'] = $product['packing_price'] + $values['packing_price'];
                        $values['vat_price'] = $product['vat_price'] + $values['vat_price'];
                    }
                }
                // Set total price values
                $values['total_price'] = $values['product_price'] + $values['discount_price'] + $values['shipping_price'] + $values['packing_price'] + $values['vat_price'];
                // Save values to order
                $order = $this->getModel('order')->createRow();
                $order->assign($values);
                $order->save();
                // Save order basket
                if (!empty($cart['product'])) {
                    foreach ($cart['product'] as $product) {
                        // Save basket
                        $basket = $this->getModel('basket')->createRow();
                        $basket->order = $order->id;
                        $basket->product = $product['product'];
                        $basket->product_price = $product['product_price'];
                        $basket->discount_price = $product['discount_price'];
                        $basket->shipping_price = $product['shipping_price'];
                        $basket->packing_price = $product['packing_price'];
                        $basket->vat_price = $product['vat_price'];
                        $basket->number = $product['number'];
                        $basket->save();
                    }
                }
                // Set invoice
                $result = Pi::api('invoice', 'order')->createInvoice($order->id);
                // unset cart
                unset($_SESSION['order']);
                // Go to payment
                $this->jump($result['invoice_url'], $result['message'], 'success');
            }   
        } else {
            //$user = Pi::api('user', 'shop')->getUserInfo();
            //$form->setData($user);
        }
        // Set view
        $this->view()->setTemplate('checkout');
        $this->view()->assign('form', $form);
        $this->view()->assign('cart', $cart);
    }

    public function levelAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get info from url
        $id = $this->params('id');
        $process = $this->params('process');
        $module = $this->params('module');
        $return = array();
        $return['status'] = 0;
        $return['data'] = '';
        $data = array();
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
                        if($delivery['status']) {
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
                            if($gateway['status']) {
                                $data['payment'][$row->id]['title'] = $gateway['title'];
                                $data['payment'][$row->id]['path'] = $gateway['path'];
                                $data['payment'][$row->id]['status'] = $gateway['status'];
                            }  
                        }
                    }
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = $data;
                    $return['data']['shipping'] = $invoice['total']['shipping'];
                    $return['data']['total'] = $invoice['total']['total_price'];
                    $return['delivery'] = $delivery['title'];
                    $return['payment'] = ($config['order_method'] == 'offline') ? __('Offline') : '';
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
}
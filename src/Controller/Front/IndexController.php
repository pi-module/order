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
use Module\Order\Form\PayForm;
use Module\Order\Form\RemoveForm;
use Module\Order\Form\OrderForm;
use Module\Order\Form\OrderFilter;
use Zend\Json\Json;

class IndexController extends ActionController
{
    public function checkoutAction()
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

    public function checkoutLevelAction()
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

    public function detailAction()
    {
        // Check user
        $this->checkUser();
        // Get order
        $id = $this->params('id');
        $order = Pi::api('order', 'order')->getOrder($id);
        // Check order
        if (empty($order)) {
           $this->jump(array('', 'action' => 'error'), __('The order not found.'));
        }
        // Check order is for this user
        if ($order['uid'] != Pi::user()->getId()) {
            $this->jump(array('', 'action' => 'error'), __('This is not your order.'));
        }
        // set view
        $this->view()->setTemplate('empty');
        $this->view()->assign('order', $order);
    }

    public function invoiceAction()
    {
        // Check user
        $this->checkUser();
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Check invoice
        if (empty($invoice) || empty($order)) {
           $this->jump(array('', 'action' => 'error'), __('The invoice not found.'));
        }
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($invoice['uid'] != Pi::user()->getId()) {
                $this->jump(array('', 'action' => 'error'), __('This is not your invoice.'));
            }
        } else {
            if (!isset($_SESSION['payment']['invoice_id']) || $_SESSION['payment']['invoice_id'] != $invoice['id']) {
                $this->jump(array('', 'action' => 'error'), __('This is not your invoice.'));
            }
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        // set view
        $this->view()->setTemplate('invoice');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
    }

    public function payAction()
    {
        // Check user
        $this->checkUser();
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoiceRandomId($id);
        // Check invoice
        if (empty($invoice)) {
           $this->jump(array('', 'action' => 'error', 'id' => 1), __('The invoice not found.'));
        }
        // Check invoice not payd
        if ($invoice['status'] != 2) {
            $this->jump(array('', 'action' => 'error', 'id' => 2), __('The invoice payd.'));
        }
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($invoice['uid'] != Pi::user()->getId()) {
                $this->jump(array('', 'action' => 'error', 'id' => 3), __('This is not your invoice.'));
            }
        } else {
            if (!isset($_SESSION['payment']['invoice_id']) || $_SESSION['payment']['invoice_id'] != $invoice['id']) {
                $this->jump(array('', 'action' => 'error', 'id' => 4), __('This is not your invoice.'));
            }
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        // Check running pay processing
        $processing = Pi::api('processing', 'order')->checkProcessing();
        if (!$processing) {
            return $this->redirect()->toRoute('', array(
                'controller' => 'index',
                'action'     => 'remove',
                'id'         => $invoice['id'],
            ));
        }
        // Set pay processing
        Pi::api('processing', 'order')->setProcessing($invoice);
        // Get gateway object
        $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
        $gateway->setInvoice($invoice);
        // Check error
        if ($gateway->gatewayError) {
            // Remove processing
            Pi::api('processing', 'order')->removeProcessing();
            $this->jump(array('', 'action' => 'result'), $gateway->gatewayError);
        }
        // Set form
        $form = new PayForm('pay', $gateway->gatewayPayForm);
        $form->setAttribute('action', $gateway->gatewayRedirectUrl);
        // Set form values
        if (!empty($gateway->gatewayPayInformation)) {
            foreach ($gateway->gatewayPayInformation as $key => $value) {
                if ($value || $value == 0) {
                    $values[$key] = $value;
                } else {
                    // Get gateway object
                    $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
                    $this->jump(array('', 'action' => 'result'), sprintf(__('Error to get %s.'), $key)); 
                }
            }
            $form->setData($values);
        } else {
            // Get gateway object
            $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
            $this->jump(array('', 'action' => 'result'), __('Error to get information.')); 
        }
        // Set view
        $this->view()->setLayout('layout-content');
        $this->view()->setTemplate('pay');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('form', $form);
    }

    public function resultAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get request
        $request = '';
        if ($this->request->isPost()) {
            $request = $this->request->getPost();
        }    
        // Check request
        if (!empty($request)) {
            // Get processing
            $processing = Pi::api('processing', 'order')->getProcessing();
            // Check processing
            if (!$processing) {
                $message = __('Your running pay processing not set');
                $this->jump(array('', 'action' => 'error'), $message);
            }
            // Check ip
            if ($processing['ip'] != Pi::user()->getIp()) {
                $message = __('Your IP address changed and processing not valid');
                $this->jump(array('', 'action' => 'error'), $message);
            }
            // Get gateway
            $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
            // verify order
            $verify = $gateway->verifyPayment($request, $processing);
            // Check error
            if ($gateway->gatewayError) {
                // Remove processing
                Pi::api('processing', 'order')->removeProcessing();
                // Url
                if (!empty($config['payment_gateway_error_url'])) {
                    $url = $config['payment_gateway_error_url'];
                } else {
                    $url = $this->url(array('', 'action' => 'error'));
                }
                // jump
                $this->jump($url, $gateway->gatewayError);
            }
            // Check status
            if ($verify['status'] == 1) {
                // Update module order / invoice and get back url
                $url = Pi::api('invoice', 'order')->updateModuleInvoice($verify['invoice']);
                // Remove processing
                Pi::api('processing', 'order')->removeProcessing();
                // jump to module
                $message = __('Your payment were successfully. Back to module');
                $this->jump($url, $message);
            } else {
                // Remove processing
                Pi::api('processing', 'order')->removeProcessing();
                $message = __('Your payment wont successfully.');
            }
        } else {
            // Remove processing
            Pi::api('processing', 'order')->removeProcessing();
            $message = __('Did not set any request');
        }
        // Set view
        $this->view()->setTemplate('result');
        $this->view()->assign('message', $message);
    }

    public function notifyAction()
    {
        // Set view
        $this->view()->setTemplate(false)->setLayout('layout-content');
        // Get module 
        $module = $this->params('module');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Get request
        $request = '';
        if ($this->request->isPost()) {
            $request = $this->request->getPost();
        }
        // Check request
        if (!empty($request)) {
            // Get processing
            $processing = Pi::api('processing', 'order')->getProcessing($request['invoice']);
            // Check processing
            if ($processing) {
                // Get gateway
                $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
                $verify = $gateway->verifyPayment($request, $processing);
                // Check error
                if ($gateway->gatewayError) {
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing($request['invoice']);
                } else {
                    if ($verify['status'] == 1) {
                        $url = Pi::api('invoice', 'order')->updateModuleInvoice($verify['invoice']);
                        Pi::api('invoice', 'order')->setBackUrl($verify['invoice'], $url);
                    }
                }
            }
        }
    }

    public function removeAction()
    {
        // Get invoice id
        $id = $this->params('id');
        // Get post
        if ($this->request->isPost()) {
            $data = $this->request->getPost()->toArray();
            if (isset($data['id']) && !empty($data['id'])) {
                Pi::api('processing', 'order')->removeProcessing();
                $message = __('Your old payment process remove, please try new payment ation');
            } else {
                $message = __('Payment is clean');
            }
            $this->jump(array('', 'action' => 'invoice', 'id' => $id), $message);
        } else {
            $processing = Pi::api('processing', 'order')->getProcessing();
            if (isset($processing['id']) && !empty($processing['id'])) {
                $values['id'] = $processing['id'];
            } else {
                $message = __('Payment is clean');
                $this->jump(array('', 'action' => 'invoice', 'id' => $id), $message);
            }
            // Set form
            $form = new RemoveForm('Remove');
            $form->setData($values);
            // Set view
            $this->view()->setTemplate('remove');
            $this->view()->assign('form', $form);
        }    
    }

    public function cancelAction()
    {
        // Set return
        $return = array(
            'website' => Pi::url(),
            'module' => $this->params('module'),
            'message' => 'finish',
        );
        // Set view
        $this->view()->setTemplate(false)->setLayout('layout-content');
        return Json::encode($return);
    }

    public function finishAction()
    {
        $processing = Pi::api('processing', 'order')->getProcessing();
        if (!empty($processing['invoice'])) {
            $invoice = $processing['invoice'];
            // remove
            Pi::api('processing', 'order')->removeProcessing();
            //
            $invoice = Pi::api('invoice', 'order')->getInvoice($invoice);
            // jump to module
            $message = __('Your payment were successfully.');
            $this->jump($invoice['back_url'], $message);
        } else {
            // Set return
            $return = array(
                'website' => Pi::url(),
                'module' => $this->params('module'),
                'message' => 'finish',
            );
            // Set view
            $this->view()->setTemplate(false)->setLayout('layout-content');
            return Json::encode($return);
        }
    }

    public function errorAction()
    {
        // Set return
        $return = array(
            'website' => Pi::url(),
            'module' => $this->params('module'),
            'message' => 'error',
            'id' => $this->params('id'),
        );
        // Set view
        $this->view()->setTemplate(false)->setLayout('layout-content');
        return Json::encode($return);  
    }

    public function checkUser()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Check config
        if ($config['order_anonymous'] == 0) {
            // Check user is login or not
            Pi::service('authentication')->requireLogin();
        }
        // Check
        if (!Pi::service('authentication')->hasIdentity()) {
            if (!isset($_SESSION['payment']['process']) || $_SESSION['payment']['process'] != 1) {
                $this->jump(array('', 'action' => 'error'));
            }
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        //
        return true;
    }
}
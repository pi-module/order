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
        $order = $_SESSION['order'];
        if (empty($order)) {
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
                // Set type
                if (isset($_SESSION['order']['type']) && in_array($_SESSION['order']['type'], array('free','onetime','recurring','installment'))) {
                    $values['type'] = $_SESSION['order']['type'];
                }
                // Set module_name
                if (isset($_SESSION['order']['module_name']) && !empty($_SESSION['order']['module_name'])) {
                    $values['type'] = $_SESSION['order']['module_name'];
                }
                // Set module_table
                if (isset($_SESSION['order']['module_table']) && !empty($_SESSION['order']['module_table'])) {
                    $values['type'] = $_SESSION['order']['module_table'];
                }
                // Set module_item
                if (isset($_SESSION['order']['module_item']) && !empty($_SESSION['order']['module_item'])) {
                    $values['type'] = $_SESSION['order']['module_item'];
                }
                // Set price
                $values['product_price'] = 0;
                $values['discount_price'] = 0;
                $values['shipping_price'] = 0;
                $values['packing_price'] = 0;
                $values['vat_price'] = 0;
                // Check order
                if (!empty($_SESSION['order']['product'])) {
                    foreach ($_SESSION['order']['product'] as $product) {
                        $values['product_price'] = $product['product_price'] + $values['product_price'];
                        $values['discount_price'] = $product['discount_price'] + $values['discount_price'];
                        $values['shipping_price'] = $product['shipping_price'] + $values['shipping_price'];
                        $values['packing_price'] = $product['packing_price'] + $values['packing_price'];
                        $values['vat_price'] = $product['vat_price'] + $values['vat_price'];
                    }
                }
                // Set total price
                $values['total_price'] = $values['product_price'] + $values['discount_price'] + $values['shipping_price'] + $values['packing_price'] + $values['vat_price'];
                // Save values to order
                $row = $this->getModel('order')->createRow();
                $row->assign($values);
                $row->save();
                // Save order basket
                if (!empty($_SESSION['order']['product'])) {
                    foreach ($_SESSION['order']['product'] as $product) {
                        $basket = $this->getModel('basket')->createRow();
                        $basket->order = $row->id;
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
                // Set payment information
                /* if ($row->gateway == 'offline') {
                    $result['status'] = 1;
                    $result['message'] = __('Your order saved and we will call you soon');
                    $result['invoice_url'] = Pi::url($this->url('', array(
                        'module'        => $this->params('module'), 
                        'controller'    => 'checkout',
                        'action'        => 'finish',
                        'id'            => $row->id,
                    )));
                } else {
                    // Set invoice description
                    $description = array();
                    foreach ($cart['product'] as $product) {
                        $item = array();
                        $item['id'] = $product['id'];
                        $item['title'] = $product['title'];
                        $item['price'] = $product['price'];
                        $item['number'] = $product['number'];
                        $item['total'] = $product['total'];
                        $description[$product['id']] = $item;
                    }
                    // Set order array
                    $order = array();
                    $order['module'] = $this->getModule();
                    $order['part'] = 'order';
                    $order['id'] = $row->id;
                    $order['amount'] = $row->total_price;
                    $order['adapter'] = $row->payment_adapter;
                    $order['description'] = Json::encode($description);
                    // Payment module
                    $result = Pi::api('invoice', 'payment')->createInvoice(
                        $order['module'], 
                        $order['part'], 
                        $order['id'], 
                        $order['amount'], 
                        $order['adapter'], 
                        $order['description']
                    );
                }
                // Check it save or not
                if ($result['status']) {
                    // unset cart
                    $this->setEmpty();
                    // Go to payment
                    $this->jump($result['invoice_url'], $result['message'], 'success');
                } else {
                    $message = __('Checkout data not saved.');
                } */
            }   
        } else {
            //$user = Pi::api('user', 'shop')->getUserInfo();
            //$form->setData($user);
        }
        // Set view
        $this->view()->setTemplate('checkout');
        $this->view()->assign('form', $form);
    }

    /* public function indexAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Get module 
        $module = $this->params('module');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $where = array('uid' => Pi::user()->getId());
        if (!$config['payment_shownotpay']) {
            $where['status'] = 1;
        }
        $select = $this->getModel('invoice')->select()->where($where)->order($order);
        $rowset = $this->getModel('invoice')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['description'] = Json::decode($list[$row->id]['description'], true);
            $list[$row->id]['user'] = Pi::user()->get($list[$row->id]['uid'], array('id', 'identity', 'name', 'email'));
            $list[$row->id]['time_create_view'] = _date($list[$row->id]['time_create']);
            $list[$row->id]['time_payment_view'] = ($list[$row->id]['time_payment']) ? _date($list[$row->id]['time_payment']) : __('Not yet');
            $list[$row->id]['amount_view'] = _currency($list[$row->id]['amount']);
        }
        // Set view
        $this->view()->setTemplate('list');
        $this->view()->assign('list', $list);
    } */

    public function invoiceAction()
    {
        // Check user
        $this->checkUser();
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        // Check invoice
        if (empty($invoice)) {
           $this->jump(array('', 'action' => 'error'), __('The invoice not found.'));
        }
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($invoice['uid'] != Pi::user()->getId()) {
                $this->jump(array('', 'action' => 'error'), __('This is not your invoice.'));
            }
        } else {
            if (!isset($_SESSION['order']['invoice_id']) || $_SESSION['order']['invoice_id'] != $invoice['id']) {
                $this->jump(array('', 'action' => 'error'), __('This is not your invoice.'));
            }
            // Set session
            $_SESSION['order']['process_update'] = time();
        }
        // set view
        $this->view()->setTemplate('invoice');
        $this->view()->assign('invoice', $invoice);
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
            if (!isset($_SESSION['order']['invoice_id']) || $_SESSION['order']['invoice_id'] != $invoice['id']) {
                $this->jump(array('', 'action' => 'error', 'id' => 4), __('This is not your invoice.'));
            }
            // Set session
            $_SESSION['order']['process_update'] = time();
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
        $gateway = Pi::api('gateway', 'order')->getGateway($invoice['adapter']);
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
                    $gateway = Pi::api('gateway', 'order')->getGateway($invoice['adapter']);
                    $this->jump(array('', 'action' => 'result'), sprintf(__('Error to get %s.'), $key)); 
                }
            }
            $form->setData($values);
        } else {
            // Get gateway object
            $gateway = Pi::api('gateway', 'order')->getGateway($invoice['adapter']);
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
            $gateway = Pi::api('gateway', 'order')->getGateway($processing['adapter']);
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
                $gateway = Pi::api('gateway', 'order')->getGateway($processing['adapter']);
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
            if (!isset($_SESSION['order']['process']) || $_SESSION['order']['process'] != 1) {
                $this->jump(array('', 'action' => 'error'));
            }
            // Set session
            $_SESSION['order']['process_update'] = time();
        }
        //
        return true;
    }
}
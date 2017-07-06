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
use Zend\Json\Json;

class PaymentController extends IndexController
{
    public function indexAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get from url
        $id = $this->params('id');
        $credit = $this->params('credit');
        $anonymous = $this->params('anonymous');
        $token = $this->params('token');
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoiceForPayment($id);
        // Check invoice
        if (empty($invoice)) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('The invoice not found.'));
        }
        // Check invoice not paid
        if ($invoice['status'] != 2) {
            $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $invoice['order']), __('You pay this invoice before this time'));
        }
        // Check invoice can pay
        if ($invoice['can_pay'] != 1) {
            $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $invoice['order']), __('You can pay this invoice after admin review'));
        }
        // Check offline
        if ($invoice['gateway'] == 'Offline') {
            $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $invoice['order']), $config['payment_offline_description']);
        }
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($invoice['uid'] != Pi::user()->getId()) {
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('This is not your invoice.'));
            }
        } else {
            if ($anonymous == 1 && !empty($token)) {
                $check = Pi::api('token', 'tools')->check($token, 'order');
                if ($check['status'] != 1) {
                    $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('Token not true'));
                }
            } elseif (!isset($_SESSION['payment']['invoice_id']) || $_SESSION['payment']['invoice_id'] != $invoice['id']) {
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('This is not your invoice.'));
            }
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        // set Products
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Check order is for this user
        if (!in_array($order['status_order'], array(1, 2, 3))) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('This order not avtice.'));
        }
        // process credit
        if ($credit == 1 && $config['credit_active'] && Pi::service('authentication')->hasIdentity() && $order['type_payment'] != 'installment') {
            $creditInformation = Pi::api('credit', 'order')->getCredit();
            if ($config['credit_type'] == 'general') {
                $creditAmount = $creditInformation['amount'];
            } elseif ($config['credit_type'] == 'module') {
                $creditAmount = $creditInformation['amount_detail_view'][$order['module_name']]['amount'];
            }
            if ($creditAmount > 0) {
                // Use credit
                if ($invoice['total_price'] > $creditAmount) {
                    // Set credit
                    $history = array(
                        'uid' => $invoice['uid'],
                        'amount' => $creditAmount,
                        'amount_old' => $creditAmount,
                        'amount_new' => 0,
                        'status_fluctuation' => 'decrease',
                        'status_action' => 'automatic',
                        'message_user' => '',
                        'message_admin' => '',
                        'module' => $order['module_name'],
                    );
                    Pi::api('credit', 'order')->addHistory($history, $order['id'], $invoice['id']);
                    // Set new price for payment
                    $invoice['total_price'] = $invoice['total_price'] - $creditAmount;
                } elseif ($invoice['total_price'] < $creditAmount) {
                    // Set credit
                    $messageAdmin = sprintf(__('use credit to pay invoice %s from order %s'), $invoice['code'], $order['code']);
                    $messageUser = sprintf(__('use credit to pay invoice %s from order %s'), $invoice['code'], $order['code']);
                    $amount = $creditAmount - $invoice['total_price'];
                    Pi::api('credit', 'order')->addCredit($invoice['uid'], $amount, 'decrease', 'automatic', $messageAdmin , $messageUser, $order['module_name']);
                    // Update invoice
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
                    // Update module order / invoice and get back url
                    $url = Pi::api('order', 'order')->updateOrder($order['id'], $invoice['id']);
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing();
                    // jump to module
                    $message = __('Your payment were successfully. Back to module');
                    $this->jump($url, $message);
                } elseif ($invoice['total_price'] == $creditAmount) {
                    // Set credit
                    $messageAdmin = sprintf(__('use credit to pay invoice %s from order %s'), $invoice['code'], $order['code']);
                    $messageUser = sprintf(__('use credit to pay invoice %s from order %s'), $invoice['code'], $order['code']);
                    $amount = 0;
                    Pi::api('credit', 'order')->addCredit($invoice['uid'], $amount, 'decrease', 'automatic', $messageAdmin , $messageUser, $order['module_name']);
                    // Update invoice
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
                    // Update module order / invoice and get back url
                    $url = Pi::api('order', 'order')->updateOrder($order['id'], $invoice['id']);
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing();
                    // jump to module
                    $message = __('Your payment were successfully. Back to module');
                    $this->jump($url, $message);
                }
            }
        }
        // Check running pay processing
        $processing = Pi::api('processing', 'order')->checkProcessing();
        if (!$processing) {
            return $this->redirect()->toRoute('', array(
                'controller' => 'index',
                'action' => 'remove',
                'id' => $invoice['id'],
            ));
        }
        // Set pay processing
        Pi::api('processing', 'order')->setProcessing($invoice);
        // Check test mode
        if ($config['order_testmode']) {
            return $this->redirect()->toRoute('', array(
                'controller' => 'payment',
                'action' => 'test',
                'id' => $invoice['id'],
            ));
        }
        // Check invoice prive
        if (in_array($order['status_order'], array(1, 2, 3)) && $invoice['status'] == 2 && $invoice['total_price'] == 0) {
            $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
            $url = Pi::api('order', 'order')->updateOrder($invoice['order'], $invoice['id']);
            // Remove processing
            Pi::api('processing', 'order')->removeProcessing();
            // jump to module
            $message = __('Your payment were successfully. Back to module');
            $this->jump($url, $message);
        }
        // Get gateway object
        $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
        $gateway->setInvoice($invoice);
        // Check error
        if ($gateway->gatewayError) {
            // Remove processing
            Pi::api('processing', 'order')->removeProcessing();
            $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), $gateway->gatewayError);
        }

        // Set form values
        if (!empty($gateway->gatewayPayInformation)) {
            foreach ($gateway->gatewayPayInformation as $key => $value) {
                if ($value || $value == 0) {
                    $values[$key] = $value;
                } else {
                    // Get gateway object
                    $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
                    $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), sprintf(__('Error to get %s.'), $key));
                }
            }
            // Set form
            $form = new PayForm('pay', $gateway->gatewayPayForm);
            $form->setAttribute('action', $gateway->gatewayRedirectUrl);
            $form->setData($values);
        } else {
            if (isset($gateway->gatewayRedirectUrl) && !empty($gateway->gatewayRedirectUrl)) {
                return $this->redirect()->toUrl($gateway->gatewayRedirectUrl);
            } else {
                // Get gateway object
                $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
                $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), __('Error to get information.'));
            }
        }
        // Set view
        $this->view()->setLayout('layout-style');
        $this->view()->setTemplate('pay');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('form', $form);
        $this->view()->assign('gateway', $gateway);
    }

    public function resultAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get request
        if ($this->request->isPost()) {
            $request = $this->request->getPost();
        } else {
            $request = _get()->toArray();
        }
        // Check request
        if (!empty($request)) {
            // Get processing
            $processing = Pi::api('processing', 'order')->getProcessing();
            // Check processing
            if (!$processing) {
                $message = __('Your running pay processing not set');
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'), $message);
            }
            // Check ip
            if ($processing['ip'] != Pi::user()->getIp()) {
                $message = __('Your IP address changed and processing not valid');
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'), $message);
            }
            // Get gateway
            $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
            // verify order
            $verify = $gateway->verifyPayment($request, $processing);
            // Check status
            if ($verify['status'] == 1) {
                // Update module order / invoice and get back url
                $url = Pi::api('order', 'order')->updateOrder($verify['order'], $verify['invoice']);
                // Remove processing
                Pi::api('processing', 'order')->removeProcessing();
                // jump to module
                $message = __('Your payment were successfully. Back to module');
                $this->jump($url, $message);
            } else {
                // Check error
                if ($gateway->gatewayError) {
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing();
                    // Url
                    if (isset($config['payment_gateway_error_url']) && !empty($config['payment_gateway_error_url'])) {
                        $url = $config['payment_gateway_error_url'];
                        $this->jump($url);
                    }
                    // jump
                    $message = $gateway->gatewayError;
                } else {
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing();
                    $message = __('Your payment wont successfully.');
                }
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
        // Get module
        $gatewayName = $this->params('gatewayName', 'paypal');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Get request
        $request = '';
        // Get request
        if ($this->request->isPost()) {
            $request = $this->request->getPost();
        } elseif (isset($_GET['invoice'])) {
            $request = _get()->toArray();
        }
        // Check request
        if (!empty($request)) {
            // Set log
            $log = array();
            $log['gateway'] = $gatewayName;
            $log['value'] = Json::encode(array(1, $request));
            Pi::api('log', 'order')->setLog($log);
            // Get processing
            $processing = Pi::api('processing', 'order')->getProcessing($request['invoice']);
            // Set log
            $log = array();
            $log['gateway'] = $gatewayName;
            $log['value'] = Json::encode(array(3, $request, $processing));
            Pi::api('log', 'order')->setLog($log);
            // Check processing
            if ($processing) {
                // Set log
                $log = array();
                $log['gateway'] = $gatewayName;
                $log['value'] = Json::encode(array(4, $request));
                Pi::api('log', 'order')->setLog($log);
                // Get gateway
                $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
                $verify = $gateway->verifyPayment($request, $processing);
                // Set log
                $log = array();
                $log['gateway'] = $gatewayName;
                $log['value'] = Json::encode(array(5, $verify));
                Pi::api('log', 'order')->setLog($log);
                // Check error
                if ($gateway->gatewayError) {
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing($request['invoice']);
                } else {
                    if ($verify['status'] == 1) {
                        $url = Pi::api('order', 'order')->updateOrder($verify['order'], $verify['invoice']);
                        Pi::api('invoice', 'order')->setBackUrl($verify['invoice'], $url);
                        // Add log
                        $log = array();
                        $log['gateway'] = $gatewayName;
                        $log['value'] = Json::encode(array(10, $verify, $url));
                        Pi::api('log', 'order')->setLog($log);
                    } else {
                        $log = array();
                        $log['gateway'] = $gatewayName;
                        $log['value'] = Json::encode(array(11, $verify));
                        Pi::api('log', 'order')->setLog($log);
                    }
                }
            } else {
                // Set log
                $log = array();
                $log['gateway'] = $gatewayName;
                $log['value'] = Json::encode(array(9, $request));
                Pi::api('log', 'order')->setLog($log);
            }
        } else {
            // Set log
            $log = array();
            $log['gateway'] = $gatewayName;
            $log['value'] = Json::encode(array(2, $request));
            Pi::api('log', 'order')->setLog($log);
        }
    }

    public function finishAction()
    {
        $type = $this->params('type');
        $paypal = false;
        if ($type == 'paypal') {
            $paypal = true;
        }
        
        $url = Pi::url($this->url('', array(
            'module' => $this->getModule(),
            'controller' => 'payment',
            'action' => 'process',
        )));

        // Set view
        $this->view()->setTemplate('finish')->setLayout('layout-style');
        $this->view()->assign('url', $url);
        $this->view()->assign('paypal', $paypal);
    }

    public function processAction()
    {
        $processing = Pi::api('processing', 'order')->getProcessing();
        if (!empty($processing['invoice'])) {
            // Get invoice
            $invoice = Pi::api('invoice', 'order')->getInvoice($processing['invoice']);
            // Remove
            Pi::api('processing', 'order')->removeProcessing();
            // Set back url
            if (isset($invoice['back_url']) && !empty($invoice['back_url'])) {
                $url = $invoice['back_url'];
            } else {
                $url = Pi::url('guide/manage/add');
            }
            // jump to module
            $message = __('Your payment were successfully.');
            $this->jump($url, $message);
        } else {
            // Set return
            $return = array(
                'website' => Pi::url(),
                'module' => $this->params('module'),
                'message' => 'process',
            );
            // Set view
            $this->view()->setTemplate(false)->setLayout('layout-content');
            return Json::encode($return);
        }
    }

    public function cancelAction()
    {
        // Set return
        $return = array(
            'website' => Pi::url(),
            'module' => $this->params('module'),
            'message' => 'cancel',
        );
        // Set view
        $this->view()->setTemplate(false)->setLayout('layout-content');
        return Json::encode($return);
    }

    public function testAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Check test mode
        if (!$config['order_testmode']) {
            // jump to module
            $url = array('', 'controller' => 'index', 'action' => 'index');
            $message = __('Test mode not active.');
            $this->jump($url, $message);
        }
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
        // Update module order / invoice and get back url
        $url = Pi::api('order', 'order')->updateOrder($invoice['order'], $invoice['id']);
        // Remove processing
        Pi::api('processing', 'order')->removeProcessing();
        // jump to module
        $message = __('Your payment were successfully. Back to module');
        $this->jump($url, $message);
    }
}
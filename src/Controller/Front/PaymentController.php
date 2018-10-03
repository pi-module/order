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
use Module\Order\Gateway\AbstractGateway;
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
        
        if (Pi::api('order', 'order')->hasPayment($id)) {
            $this->jump(array('', 'action' => 'index'), __('Order has payment. You cannont access to this page'));    
        }
        
        $credit = $this->params('credit');
        $anonymous = $this->params('anonymous');
        $token = $this->params('token');
        $order  = Pi::api('order', 'order')->getOrder($id);
        if (empty($order)) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('The order not found.'));
        }

        if ($order['status_order'] != \Module\Order\Model\Order::STATUS_ORDER_VALIDATED) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('This order not actice.'));
        }
        
        // Check offline
        $cart = Pi::api('order', 'order')->getOrderInfo();
        if (!is_array($cart)) {
            $cart = array();
        }
        if (!isset($cart['gateway']) || $cart['gateway'] == null) {
            $order['installments'] = Pi::api('installment', 'order')->getInstallmentsFromOrder($order['id']);
            $cart['gateway'] = $order['default_gateway'];
            
            foreach ($order['installments'] as $installment) {
                if ($installment['status_invoice'] != \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED) {
                    if ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID) {
                        $cart['gateway'] = $installment['gateway'];
                        break;
                    }
                }
            }
        }
        
        if ($cart['gateway'] == 'Offline') {
            $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $id), $config['payment_offline_description']);
        }
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($order['uid'] != Pi::user()->getId()) {
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('This is not your order.'));
            }
        } else {
            if ($anonymous == 1 && !empty($token)) {
                $check = Pi::api('token', 'tools')->check($token, 'order');
                if ($check['status'] != 1) {
                    $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('Token not true'));
                }
            } 
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }

        $products = Pi::api('order', 'order')->listProduct($order['id']);
        $processing = Pi::api('processing', 'order')->getProcessing();
        
        // process credit
        if ($credit == 1 && $config['credit_active'] && Pi::service('authentication')->hasIdentity()) {
            
            // determine module
            foreach ($products as $product) {
                $module = $product['module'];
            }
            
            $creditInformation = Pi::api('credit', 'order')->getCredit();
            if ($config['credit_type'] == 'general') {
                $creditAmount = $creditInformation['amount'];
            } elseif ($config['credit_type'] == 'module') {
                $creditAmount = $creditInformation['amount_detail_view'][$module]['amount'];
            }
            if ($creditAmount > 0) {
                // Use credit
                if ($order['total_price'] > $creditAmount) {
                    // Set credit
                    $history = array(
                        'uid' => $order['uid'],
                        'amount' => $creditAmount,
                        'amount_old' => $creditAmount,
                        'amount_new' => 0,
                        'status_fluctuation' => 'decrease',
                        'status_action' => 'automatic',
                        'message_user' => '',
                        'message_admin' => '',
                        'module' => $module,
                    );
                    Pi::api('credit', 'order')->addHistory($history, $order['id'], $invoice['id']);
                    // Set new price for payment
                    $order['total_price'] = $order['total_price'] - $creditAmount;
                } elseif ($order['total_price'] < $creditAmount) {
                    // Set credit
                    $messageAdmin = sprintf(__('use credit to pay order %s'),  $order['code']);
                    $messageUser = sprintf(__('use credit to pay order %s'), $order['code']);
                    $amount = $creditAmount - $order['total_price'];
                    Pi::api('credit', 'order')->addCredit($order['uid'], $amount, 'decrease', 'automatic', $messageAdmin , $messageUser, $module);
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id'], $processing['gateway']);
                    // Update module order / invoice and get back url
                    $url = Pi::api('order', 'order')->updateOrder($order['id'], $invoice['id']);
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing();
                    // jump to module
                    $message = __('Your payment were successfully. Back to module');
                    $this->jump($url, $message);
                } elseif ($order['total_price'] == $creditAmount) {
                    // Set credit
                    $messageAdmin = sprintf(__('use credit to pay order %s'), $order['code']);
                    $messageUser = sprintf(__('use credit to pay order %s'), $order['code']);
                    $amount = 0;
                    Pi::api('credit', 'order')->addCredit($order['uid'], $amount, 'decrease', 'automatic', $messageAdmin , $messageUser, $module);
                    // Update invoice
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id'], $processing['gateway']);
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
            Pi::api('processing', 'order')->removeProcessing();
        }


        // Set pay processing
        Pi::api('processing', 'order')->setProcessing($order, $cart['gateway']);
        $processing = Pi::api('processing', 'order')->getProcessing();
        if ($config['order_testmode']) {
            return $this->redirect()->toRoute('', array(
                'controller' => 'payment',
                'action' => 'test',
                'id' => $order['id'],
            ));
        } 
        // Check invoice price
        $totalPrice = 0;
        foreach ($products as $product) {
            $totalPrice = $product['product_price'] + $product['shipping_price'] + $product['packing_price'] + $product['setup_price'] + $product['vat_price'];
        }
        if ($order['status_order'] == \Module\Order\Model\Order::STATUS_ORDER_VALIDATED  && $totalPrice == 0) {
            $uid = Pi::user()->getId();
            if (!$uid) {
                  $uid = $_SESSION['order']['uid']; 
            }
            $invoice = Pi::api('invoice', 'order')->createInvoice($order['id'], $uid);
            $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id'], $processing['gateway']);
            $url = Pi::api('order', 'order')->updateOrder($invoice['order'], $invoice['id']);
            // Remove processing
            Pi::api('processing', 'order')->removeProcessing();
            // jump to module
            $message = __('Your payment were successfully. Back to module');
            $this->jump($url, $message);
        }
        // Get gateway object
        $gateway = Pi::api('gateway', 'order')->getGateway($cart['gateway']);
        $gateway->setOrder($order);
        // Check error
        if ($gateway->gatewayError) {
            // Remove processing
            Pi::api('processing', 'order')->removeProcessing();
            $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), $gateway->gatewayError);
        }

        if ($gateway->getType() == AbstractGateway::TYPE_REST) {
            $approvalUrl = $gateway->getApproval($order);
            if (!$approvalUrl) {
                $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), __('Error to get information.'));
            }
            return $this->redirect()->toUrl($approvalUrl);
        }

        // Set form values
        if (!empty($gateway->gatewayPayInformation)) {
            foreach ($gateway->gatewayPayInformation as $key => $value) {
                if ($value || $value == 0) {
                    $values[$key] = $value;
                } else {
                    // Get gateway object
                    $gateway = Pi::api('gateway', 'order')->getGateway($order['gateway']);
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
                $gateway = Pi::api('gateway', 'order')->getGateway($order['gateway']);
                $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), __('Error to get information.'));
            }
        }
        // Set view
        $this->view()->setLayout('layout-style');
        $this->view()->setTemplate('pay');
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
        $processing = Pi::api('processing', 'order')->getProcessing();

        // Check request
        if (!empty($processing)) {
            // Get processing
            Pi::api('order', 'order')->unsetOrderInfo();

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
            $order  = Pi::api('order', 'order')->getOrder($processing['order']);
            $gateway->setOrder($order);
            $verify = $gateway->verifyPayment($request, $processing);
            // Check status
            if ($verify['status'] == 1) {
                // Update module order / invoice and get back url
                $invoice = Pi::api('invoice', 'order')->getInvoiceFromOrder($processing['order']);
                $randomId = $invoice['random_id'];
                if (empty($invoice)) {
                    $result = Pi::api('invoice', 'order')->createInvoice($processing['order'], Pi::user()->getId());
                    $randomId = $result['random_id'];
                } 
                $invoice = Pi::api('invoice', 'order')->updateInvoice($result['random_id'], $processing['gateway']);
                $url = Pi::api('order', 'order')->updateOrder($verify['order'], $invoice['id']);
                
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
        $gatewayName = $this->params('gatewayName', 'Paypal');
        // Get request
        $request = '';
        // Get request
        if ($this->request->isPost()) {
            $request = $this->request->getPost();
        } elseif (isset($_GET['invoice'])) {
            $request = _get()->toArray();
        }
        // Check request
        if (!empty($request) && in_array($gatewayName, array(
            'Paypal', 'paypal', 'Bitcoin', 'bitcoin'
            ))) {
            // Get random_id
            switch ($gatewayName) {
                case 'Paypal':
                case 'paypal':
                    $randomID = $request['invoice'];
                    break;

                case 'Bitcoin':
                case 'bitcoin':
                    $randomID = $request['orderId'];
                    break;
            }
            // Set log
            $log = array();
            $log['gateway'] = $gatewayName;
            $log['value'] = Json::encode(array(1, $request));
            Pi::api('log', 'order')->setLog($log);
            // Get processing
            $processing = Pi::api('processing', 'order')->getProcessing($randomID);
            // Set log
            $log = array();
            $log['gateway'] = $gatewayName;
            $log['uid'] = $processing['uid'];
            $log['value'] = Json::encode(array(3, $request, $processing));
            Pi::api('log', 'order')->setLog($log);
            // Check processing
            if ($processing) {
                // Set log
                $log = array();
                $log['gateway'] = $gatewayName;
                $log['uid'] = $processing['uid'];
                $log['value'] = Json::encode(array(4, $request, $processing, $randomID));
                Pi::api('log', 'order')->setLog($log);
                // Get gateway
                $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
                $verify = $gateway->verifyPayment($request, $processing);
                // Set log
                $log = array();
                $log['gateway'] = $gatewayName;
                $log['uid'] = $processing['uid'];
                $log['value'] = Json::encode(array(5, $verify));
                Pi::api('log', 'order')->setLog($log);
                // Check error
                if ($gateway->gatewayError) {
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing($randomID);
                } else {
                    if ($verify['status'] == 1) {
                        $url = Pi::api('order', 'order')->updateOrder($verify['order'], $verify['invoice']);
                        Pi::api('invoice', 'order')->setBackUrl($verify['invoice'], $url);
                        // Add log
                        $log = array();
                        $log['gateway'] = $gatewayName;
                        $log['uid'] = $processing['uid'];
                        $log['value'] = Json::encode(array(10, $verify, $url));
                        Pi::api('log', 'order')->setLog($log);
                    } else {
                        $log = array();
                        $log['gateway'] = $gatewayName;
                        $log['uid'] = $processing['uid'];
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
        
        $processing = Pi::api('processing', 'order')->getProcessing();
        if (!empty($processing['order'])) {
            // Get invoice
            $uid = Pi::user()->getId();
            if (!$uid) {
                  $uid = $_SESSION['order']['uid']; 
            }
            
            $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
            $specificBackurl = null;
            if ($gateway->getType() == AbstractGateway::TYPE_REST) {
                $specificBackurl = $_SESSION['order']['redirect']; 
                $paymentId = $this->params('paymentId');
                $payerId = $this->params('PayerID');
                $order  = Pi::api('order', 'order')->getOrder($processing['order']);
                $gateway->setOrder($order);
                $result = $gateway->execute($payerId, $paymentId);
                if ($result->state == 'approved') {
                    $url = Pi::url($this->url('', array(
                        'module' => $this->getModule(),
                        'controller' => 'payment',
                        'action' => 'process',
                    )));
                    $result = Pi::api('invoice', 'order')->createInvoice($processing['order'], $uid);
                    $invoice = Pi::api('invoice', 'order')->updateInvoice($result['random_id'], $processing['gateway']);
                    $backurl = Pi::api('order', 'order')->updateOrder($invoice['order'], $invoice['id']);
                    Pi::api('invoice', 'order')->setBackUrl($invoice['id'], $specificBackurl ?: $backurl);
                    $messenger = $this->plugin('flashMessenger');
                } else {
                    $url = Pi::url($this->url('', array(
                        'module' => $this->getModule(),
                        'controller' => 'payment',
                        'action' => 'result',
                    )));
                    $url .= '?order=' .  $order['id']; 
                }
                return $this->redirect($url);
            }
        } else {
            
            $result = Pi::api('invoice', 'order')->createInvoice($processing['order'], Pi::user()->getId());
            $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id'], $processing['gateway']);
            Pi::api('order', 'order')->unsetOrderInfo();
            
            $url = array('', 'controller' => 'index', 'action' => 'index');
            $message = __('Order canceled');
            return $this->jump($url, $message);
        }
        // Set view
        $this->view()->setTemplate('finish')->setLayout('layout-style');
        $this->view()->assign('url', $url);
        $this->view()->assign('paypal', $paypal);
    }

    public function processAction()
    {
        $processing = Pi::api('processing', 'order')->getProcessing();
        if (!empty($processing['order'])) {
            // Get invoice
            $invoice = current(Pi::api('invoice', 'order')->getInvoiceFromOrder($processing['order']));
            // Remove
            Pi::api('processing', 'order')->removeProcessing();
            // Set back url
            if (isset($invoice['back_url']) && !empty($invoice['back_url'])) {
                $url = $invoice['back_url'];
            } else {
                $url = Pi::url('guide/manage/add');
            }
            Pi::api('order', 'order')->unsetOrderInfo();
            // jump to module
            $message = __('Your payment were successfully.');
            $this->jump($url, $message);
        } else {
            Pi::api('order', 'order')->unsetOrderInfo();
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
        $processing = Pi::api('processing', 'order')->getProcessing();
        if (!empty($processing['order'])) {
            // Get invoice
            $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
            if ($gateway->getType() == AbstractGateway::TYPE_REST) {
                Pi::api('processing', 'order')->removeProcessing();
                
                $log = array();
                $log['gateway'] = $gateway->gatewayAdapter;
                $log['value'] = '{}';
                $log['message'] = 'cancel';
                $log['invoice'] = $invoice['random_id'];
                Pi::api('log', 'order')->setLog($log);
        
                $url = array('', 'controller' => 'index', 'action' => 'index');
                $message = __('Payment canceled');
                return $this->jump($url, $message);
            }
        }
        
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
        $processing = Pi::api('processing', 'order')->getProcessing();
        $invoice = Pi::api('invoice', 'order')->getInvoiceFromOrder($processing['order']);
        $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id'], $processing['gateway']);
        // Update module order / invoice and get back url
        $url = Pi::api('order', 'order')->updateOrder($invoice['order'], $invoice['id']);
        // Remove processing
        Pi::api('processing', 'order')->removeProcessing();
        // jump to module
        $message = __('Your payment were successfully. Back to module');
        $this->jump($url, $message);
    }
}

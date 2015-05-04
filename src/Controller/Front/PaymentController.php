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
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoiceForPayment($id);
        // Check invoice
        if (empty($invoice)) {
           $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('The invoice not found.'));
        }
        // Check invoice not payd
        if ($invoice['status'] != 2) {
            $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $invoice['order']), __('You pay this invoice before this time'));
        }
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($invoice['uid'] != Pi::user()->getId()) {
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('This is not your invoice.'));
            }
        } else {
            if (!isset($_SESSION['payment']['invoice_id']) || $_SESSION['payment']['invoice_id'] != $invoice['id']) {
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
        // Check test mode
        if ($config['order_testmode']) {
            return $this->redirect()->toRoute('', array(
                'controller' => 'payment',
                'action'     => 'test',
                'id'         => $invoice['id'],
            ));
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
                    $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), sprintf(__('Error to get %s.'), $key)); 
                }
            }
            $form->setData($values);
        } else {
            // Get gateway object
            $gateway = Pi::api('gateway', 'order')->getGateway($invoice['gateway']);
            $this->jump(array('', 'controller' => 'payment', 'action' => 'result'), __('Error to get information.')); 
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
            // Check error
            if ($gateway->gatewayError) {
                // Remove processing
                Pi::api('processing', 'order')->removeProcessing();
                // Url
                if (!empty($config['payment_gateway_error_url'])) {
                    $url = $config['payment_gateway_error_url'];
                } else {
                    $url = $this->url(array('', 'controller' => 'index', 'action' => 'error'));
                }
                // jump
                $this->jump($url, $gateway->gatewayError);
            }
            // Check status
            if ($verify['status'] == 1) {
                // Update module order / invoice and get back url
                $url = Pi::api('order', 'order')->updateOrder($verify['order']);
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
            // Set log
            $log = array();
            $log['gateway'] = 'paypal';
            $log['value'] = Json::encode(array(1, $request));
            Pi::api('log', 'order')->setLog($log);
            // Get processing
            $processing = Pi::api('processing', 'order')->getProcessing($request['invoice']);
            // Set log
            $log = array();
            $log['gateway'] = 'paypal';
            $log['value'] = Json::encode(array(3, $request, $processing));
            Pi::api('log', 'order')->setLog($log);
            // Check processing
            if ($processing) {
                // Set log
                $log = array();
                $log['gateway'] = 'paypal';
                $log['value'] = Json::encode(array(4, $request));
                Pi::api('log', 'order')->setLog($log);
                // Get gateway
                $gateway = Pi::api('gateway', 'order')->getGateway($processing['gateway']);
                $verify = $gateway->verifyPayment($request, $processing);
                // Set log
                $log = array();
                $log['gateway'] = 'paypal';
                $log['value'] = Json::encode(array(5, $verify));
                Pi::api('log', 'order')->setLog($log);
                // Check error
                if ($gateway->gatewayError) {
                    // Remove processing
                    Pi::api('processing', 'order')->removeProcessing($request['invoice']);
                } else {
                    if ($verify['status'] == 1) {
                        $url = Pi::api('order', 'order')->updateOrder($verify['order']);
                        Pi::api('invoice', 'order')->setBackUrl($verify['invoice'], $url);

                        $log = array();
                        $log['gateway'] = 'paypal';
                        $log['value'] = Json::encode(array(10, $verify, $url));
                        Pi::api('log', 'order')->setLog($log);
                    } else {
                        $log = array();
                        $log['gateway'] = 'paypal';
                        $log['value'] = Json::encode(array(11, $verify));
                        Pi::api('log', 'order')->setLog($log);
                    }
                }
            } else {
                // Set log
                $log = array();
                $log['gateway'] = 'paypal';
                $log['value'] = Json::encode(array(9, $request));
                Pi::api('log', 'order')->setLog($log); 
            }
        } else {
            // Set log
            $log = array();
            $log['gateway'] = 'paypal';
            $log['value'] = Json::encode(array(2, $request));
            Pi::api('log', 'order')->setLog($log);
        }
    }

    public function finishAction()
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
                'website'  => Pi::url(),
                'module'   => $this->params('module'),
                'message'  => 'finish',
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
            'website'  => Pi::url(),
            'module'   => $this->params('module'),
            'message'  => 'cancel',
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
        $url = Pi::api('order', 'order')->updateOrder($invoice['order']);
        // Remove processing
        Pi::api('processing', 'order')->removeProcessing();
        // jump to module
        $message = __('Your payment were successfully. Back to module');
        $this->jump($url, $message);
    }
}
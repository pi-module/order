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
use Zend\Json\Json;

class IndexController extends ActionController
{
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
        if ($config['payment_anonymous'] == 0) {
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
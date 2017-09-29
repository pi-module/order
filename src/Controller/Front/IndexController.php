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
use Module\Order\Form\RemoveForm;
use Zend\Json\Json;

class IndexController extends ActionController
{
    public function indexAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get user info
        $user = Pi::api('user', 'order')->getUserInformation();
        // Get order
        $orders = Pi::api('order', 'order')->getOrderFromUser($user['id'], true);
        foreach ($orders as $order) {
            $user['orders'][$order['id']] = $order;
            $user['orders'][$order['id']]['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        }
        // Set order ids
        /* $orderIds = array();
        foreach ($user['orders'] as $order) {
            $orderIds[] = $order['id'];
        }
        // Get invoice
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id'], false, $orderIds);
        // Get credit
        if ($config['credit_active']) {
            $credit = $this->getModel('credit')->find($user['id'], 'uid')->toArray();
            $credit['amount_view'] = Pi::api('api', 'order')->viewPrice($credit['amount']);
            $credit['time_update_view'] = ($credit['time_update'] > 0) ? _date($credit['time_update']) : __('Never update');
            $this->view()->assign('credit', $credit);
        } */
        // Set view
        $this->view()->setTemplate('list');
        $this->view()->assign('user', $user);
        $this->view()->assign('config', $config);
    }

    public function removeAction()
    {
        // Get invoice id
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        // Check invoice
        if ($invoice) {
            // Get post
            if ($this->request->isPost()) {
                $data = $this->request->getPost()->toArray();
                if (isset($data['id']) && !empty($data['id'])) {
                    Pi::api('processing', 'order')->removeProcessing();
                    $message = __('Your old payment process remove, please try new payment ation');
                } else {
                    $message = __('Payment is clean');
                }
                $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $invoice['order']), $message);
            } else {
                $processing = Pi::api('processing', 'order')->getProcessing();
                if (isset($processing['id']) && !empty($processing['id'])) {
                    $values['id'] = $processing['id'];
                } else {
                    $message = __('Payment is clean');
                    $this->jump(array('', 'controller' => 'detail', 'action' => 'index', 'id' => $invoice['order']), $message);
                }
                // Set form
                $form = new RemoveForm('Remove');
                $form->setData($values);
                // Set view
                $this->view()->setTemplate('remove');
                $this->view()->assign('form', $form);
            }
        } else {
            $message = __('Please select invoice');
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), $message);
        }
    }

    public function errorAction()
    {
        // Set view
        $this->view()->setTemplate('error');
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
                $_SESSION['payment']['process'] = 1;
                $_SESSION['payment']['process_start'] = time();
            }
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        //
        return true;
    }
}
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

class InvoiceController extends IndexController
{
    /* public function indexAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        // Check invoice
        if (empty($invoice)) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('The invoice not found.'));
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
        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Check invoice prive
        if (in_array($order['status_order'], array(1, 2, 3)) && $invoice['status'] == 2 && $invoice['total_price'] == 0) {
            $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
            $url = Pi::api('order', 'order')->updateOrder($invoice['order'], $invoice['id']);
            // jump to module
            $message = __('Your payment were successfully. Back to module');
            $this->jump($url, $message);
        }
        // Get logs
        $invoice['log'] = Pi::api('log', 'order')->getTrueLog($invoice['id']);
        // Check allow payment
        if ($order['type_payment'] == 'installment') {
            if ($invoice['extra']['type'] == 'installment') {
                $time = time() + (60 * 60 * 24 * 14);
                if ($invoice['time_duedate'] < $time) {
                    $allowPayment = true;
                } else {
                    $allowPayment = false;
                }
            } else {
                $allowPayment = true;
            }
        } else {
            $allowPayment = true;
        }
        // set view
        $this->view()->setTemplate('invoice');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
        $this->view()->assign('config', $config);
        $this->view()->assign('allowPayment', $allowPayment);
    } */

    public function printAction()
    {
        // Check user
        $this->checkUser();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        // Check invoice
        if (empty($invoice)) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'error'), __('The invoice not found.'));
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
        if (!in_array($order['status_order'], array(1, 2, 3, 7))) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('This order not avtice.'));
        }
        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Check invoice prive
        if (in_array($order['status_order'], array(1, 2, 3)) && $invoice['status'] == 2 && $invoice['total_price'] == 0) {
            $invoice = Pi::api('invoice', 'order')->updateInvoice($invoice['random_id']);
            $url = Pi::api('order', 'order')->updateOrder($invoice['order']);
            // jump to module
            $message = __('Your payment were successfully. Back to module');
            $this->jump($url, $message);
        }
        
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // set view
        $this->view()->setTemplate('invoice-print')->setLayout('layout-content');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
        $this->view()->assign('address', $address);
        $this->view()->assign('config', $config);
    }
}

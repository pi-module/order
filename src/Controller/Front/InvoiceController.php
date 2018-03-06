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
        
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        
        // Check invoice is for this user
        if (Pi::service('authentication')->hasIdentity()) {
            if ($order['uid'] != Pi::user()->getId()) {
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
        // Check order is for this user
        if ($order['status_order'] != \Module\Order\Model\Order::STATUS_ORDER_VALIDATED) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), __('This order not avtice.'));
        }
        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id']);
        $totalPrice = 0;
        foreach ($order['products'] as $product) {
            $totalPrice = $product['product_price'] + $product['shipping_price'] + $product['packing_price'] + $product['setup_price'] + $product['vat_price'];
        }
        // Check invoice prive
        if ($order['status_order'] == \Module\Order\Model\Order::STATUS_ORDER_VALIDATED && $invoice['status'] == 2 && $totalPrice == 0) {
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

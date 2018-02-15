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
        $orders = Pi::api('order', 'order')->getOrderFromUser($user['id'], false);
        foreach ($orders as $order) {
            $user['orders'][$order['id']] = $order;
            $user['orders'][$order['id']]['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
            $user['orders'][$order['id']]['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromOrder($order['id']);
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
                $this->jump(array('', 'controller' => 'index', 'action' => 'error'));
            }
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        //
        return true;
    }
    
    public function cancelAction()
    {
        $id = $this->params('id');
        Pi::api('order', 'order')->cancelOrder($id);
        $this->jump(array('', 'action' => 'index'), __('Order canceled'));
        
    }
    
    public function printAction()
    {
        // Check user
        $this->checkUser();
        
        $id = $this->params('id');
        $ret = Pi::api('order', 'order')->pdf($id);
        if (!$ret['status']) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), $ret['message']);
        }
    }
    
}

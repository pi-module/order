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
use Pi\Paginator\Paginator;

class IndexController extends ActionController
{
    public function indexAction()
    {
        Pi::service('authentication')->requireLogin();
        
        $config = Pi::service('registry')->config->read($this->getModule());
        $user = Pi::api('user', 'order')->getUserInformation();
        
        $page = $this->params('page', 1);
        $offset = (int)($page - 1) * $this->config('view_perpage');
        $limit = intval($this->config('view_perpage'));
        
        $options = array (
            'limit' => $limit,
            'offset' => $offset,
            'draft' => false
        );
        $orders = Pi::api('order', 'order')->getOrderFromUser($user['id'], false, $options);
        foreach ($orders as $order) {
            $order['has_payment'] = Pi::api('order', 'order')->hasPayment($order['id']);
            $user['orders'][$order['id']] = $order;
            $products = Pi::api('order', 'order')->listProduct($order['id']);
            $user['orders'][$order['id']]['products'] = $products;
            $totalPrice = 0;
            foreach ($products as $product) {
                $totalPrice += $product['product_price'] + $product['shipping_price'] + $product['packing_price'] + $product['setup_price'] + $product['vat_price'] - $product['discount_price'];
            }
            $user['orders'][$order['id']]['total_price_view'] = Pi::api('api', 'order')->viewPrice($totalPrice);
        }
        
        // Set paginator
        $count = count(Pi::api('order', 'order')->getOrderFromUser($user['id'], false));
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => 'order',
                'controller' => 'index',
                'action' => 'index',
            )),
        ));
        
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
        $this->view()->assign('paginator', $paginator);
        
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
            // Set session
            $_SESSION['payment']['process_update'] = time();
        }
        //
        return true;
    }
    
    public function cancelAction()
    {
        $id = $this->params('id');
        if (Pi::api('order', 'order')->hasPayment($id)) {
            $this->jump(array('', 'action' => 'index'), __('Order has payment. You cannont cancel it'));    
        }
        
        Pi::api('order', 'order')->cancelOrder($id);
        $this->jump(array('', 'action' => 'index'), __('Order canceled'));
        
    }
    
}

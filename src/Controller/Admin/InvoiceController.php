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

namespace Module\Order\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\Order\Form\InvoiceSettingForm;
use Module\Order\Form\InvoiceSettingFilter;
use Module\Order\Form\InvoiceForm;
use Module\Order\Form\InvoiceFilter;
use Zend\Json\Json;
use Zend\Db\Sql\Predicate\Expression;

class InvoiceController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $module = $this->params('module');
        $orderid = $this->params('orderid');
        $randomid = $this->params('randomid');
        $uid = $this->params('uid');
        $payment_status = $this->params('payment_status');
        $start = $this->params('start');
        $end = $this->params('end');
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $where = array();
        // Set where
        if ($orderid) {
            $where['order'] = $orderid;
        }
        if ($randomid) {
            $where['random_id'] = $randomid;
        }
        if ($uid) {
            $where['uid'] = $uid;
        }
        if ($payment_status) {
            if ($payment_status == 'delayed') {
                $where['status'] = 2;
                $where['time_duedate <= ?'] = time();
            } elseif (in_array($payment_status, array(1, 2))) {
                $where['status'] = array(1, 2);
            }
        }
        if ($start) {
            $where['time_duedate >= ?'] = strtotime($start);
        }
        if ($end) {
            $where['time_duedate <= ?'] = strtotime($end);
        }
        // Select
        $select = $this->getModel('invoice')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('invoice')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = Pi::api('invoice', 'order')->canonizeInvoice($row);
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = $this->getModel('invoice')->select()->where($where)->columns($count);
        $count = $this->getModel('invoice')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => $this->getModule(),
                'controller' => 'invoice',
                'action' => 'index',
                'orderid' => $orderid,
                'randomid' => $randomid,
                'uid' => $uid,
                'payment_status' => $payment_status,
                'start' => $start,
                'end' => $end,
            )),
        ));
        // Set form
        $values = array(
            'orderid' => $orderid,
            'randomid' => $randomid,
            'uid' => $uid,
            'payment_status' => $payment_status,
            'start' => $start,
            'end' => $end,
        );
        $form = new InvoiceSettingForm('setting');
        $form->setAttribute('action', $this->url('', array('action' => 'process')));
        $form->setData($values);
        // Set view
        $this->view()->setTemplate('invoice-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
        $this->view()->assign('form', $form);
    }

    public function processAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new InvoiceSettingForm('setting');
            $form->setInputFilter(new InvoiceSettingFilter());
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $message = __('Go to filter');
                $url = array(
                    'action' => 'index',
                    'orderid' => $values['orderid'],
                    'randomid' => $values['randomid'],
                    'uid' => $values['uid'],
                    'payment_status' => $values['payment_status'],
                    'start' => $values['start'],
                    'end' => $values['end'],
                );
            } else {
                $message = __('Not valid');
                $url = array(
                    'action' => 'index',
                );
            }
        } else {
            $message = __('Not set');
            $url = array(
                'action' => 'index',
            );
        }
        return $this->jump($url, $message);
    }

    public function viewAction()
    {
        // Get id
        $id = $this->params('id');
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get info
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Check invoice
        if (empty($invoice) || empty($order)) {
            $this->jump(array('', 'action' => 'index'), __('The invoice not found.'));
        }
        // Get logs
        $invoice['log'] = Pi::api('log', 'order')->getLog($invoice['id']);
        // set view
        $this->view()->setTemplate('invoice-view');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
        $this->view()->assign('config', $config);
    }

    public function printAction()
    {
        // Get id
        $id = $this->params('id');
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get info
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Check invoice
        if (empty($invoice) || empty($order)) {
            $this->jump(array('', 'action' => 'index'), __('The invoice not found.'));
        }
        // set view
        $this->view()->setTemplate('invoice-print')->setLayout('layout-content');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
        $this->view()->assign('config', $config);
    }

    public function addAction()
    {
        // Get id
        $order = $this->params('order');
        // Get order
        $order = $this->getModel('order')->find($order);
        $order = Pi::api('order', 'order')->canonizeOrder($order);
        // Set form
        $form = new InvoiceForm('setting');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new InvoiceFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $values['time_duedate'] = strtotime($values['time_duedate']);
                $values['total_price'] = $values['product_price'] + $values['shipping_price'] + $values['packing_price'] + $values['setup_price'] + $values['vat_price'];
                $values['random_id'] = time() + rand(100, 999);
                $values['uid'] = $order['uid'];
                $values['ip'] = Pi::user()->getIp();
                $values['status'] = 2;
                $values['time_create'] = time();
                $values['order'] = $order['id'];
                $values['discount_price'] = 0;
                $values['paid_price'] = 0;
                $values['credit_price'] = 0;
                $values['gateway'] = $order['gateway'];
                // Set extra
                if ($order['type_payment'] == 'installment') {
                    $extra = array();
                    $extra['order']['type_payment'] = $order['type_payment'];
                    $extra['order']['type_commodity'] = $order['type_commodity'];
                    $extra['number'] = '';
                    $extra['type'] = 'additional';
                    $values['extra'] = json::encode($extra);
                }
                // Save values
                $row = $this->getModel('invoice')->createRow();
                $row->assign($values);
                $row->save();
                // Set order ID
                $code = Pi::api('invoice', 'order')->generatCode($row->id);
                $this->getModel('invoice')->update(
                    array('code' => $code),
                    array('id' => $row->id)
                );
                // Update order
                $this->getModel('order')->update(
                    array(
                        'product_price' => $order['product_price'] + $row->product_price,
                        'shipping_price' => $order['shipping_price'] + $row->shipping_price,
                        'packing_price' => $order['packing_price'] + $row->packing_price,
                        'setup_price' => $order['setup_price'] + $row->setup_price,
                        'vat_price' => $order['vat_price'] + $row->vat_price,
                        'total_price' => $order['total_price'] + $row->total_price,
                    ),
                    array('id' => $order['id'])
                );
                // Check it save or not
                $message = __('New invoice data saved successfully.');
                $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order['id']), $message);
            }
        }
        // Set view
        $this->view()->setTemplate('invoice-add');
        $this->view()->assign('form', $form);
        $this->view()->assign('order', $order);
    }

    public function editAction()
    {
        // Get id
        $id = $this->params('id');
        // Get invoice and order
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Check invoice
        if ($invoice['status'] != 2) {
            $this->jump(
                array('controller' => 'order', 'action' => 'view', 'id' => $invoice['order']),
                __('This invoice paid or canceled before, than you can not edit it'),
                'error'
            );
        }
        // Set form
        $form = new InvoiceForm('invoice');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new InvoiceFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $values['ip'] = Pi::user()->getIp();
                $values['time_duedate'] = strtotime($values['time_duedate']);
                $values['total_price'] = $values['product_price'] + $values['shipping_price'] + $values['packing_price'] + $values['setup_price'] + $values['vat_price'];
                // Save values
                $row = $this->getModel('invoice')->find($id);
                $row->assign($values);
                $row->save();
                // Update order
                $this->getModel('order')->update(
                    array(
                        'product_price' => ($order['product_price'] - $invoice['product_price']) + $row->product_price,
                        'shipping_price' => ($order['shipping_price'] - $invoice['shipping_price']) + $row->shipping_price,
                        'packing_price' => ($order['packing_price'] - $invoice['packing_price']) + $row->packing_price,
                        'setup_price' => ($order['setup_price'] - $invoice['setup_price']) + $row->setup_price,
                        'vat_price' => ($order['vat_price'] - $invoice['vat_price']) + $row->vat_price,
                        'total_price' => ($order['total_price'] - $invoice['total_price']) + $row->total_price,
                    ),
                    array('id' => $order['id'])
                );
                // Check it save or not
                $message = __('Your invoice data saved successfully.');
                $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order['id']), $message);
            }
        } else {
            $invoice['time_duedate'] = date('Y-m-d', $invoice['time_duedate']);
            $form->setData($invoice);
        }
        // Set view
        $this->view()->setTemplate('invoice-edit');
        $this->view()->assign('form', $form);
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
    }
}
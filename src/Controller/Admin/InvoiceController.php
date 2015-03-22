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
use Zend\Json\Json;

class InvoiceController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $module = $this->params('module');
        $orderid = $this->params('orderid');
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
        if ($uid) {
            $where['uid'] = $uid;
        }
        if ($payment_status) {
            if ($payment_status == 'delayed') {
                $where['status'] = 2;
                $where['time_duedate <= ?'] = time();
            } elseif (in_array($payment_status, array(1, 2))) {
                $where['status'] = $payment_status;
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
        $count = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = $this->getModel('invoice')->select()->columns($count);
        $count = $this->getModel('invoice')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'         => $this->getModule(),
                'controller'     => 'invoice',
                'action'         => 'index',
                'orderid'        => $orderid,
                'uid'            => $uid,
                'payment_status' => $payment_status,
                'start'          => $start,
                'end'            => $end,
            )),
        ));
        // Set form
        $values = array(
            'orderid'        => $orderid,
            'uid'            => $uid,
            'payment_status' => $payment_status,
            'start'          => $start,
            'end'            => $end,
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
                    'action'         => 'index',
                    'orderid'        => $values['orderid'],
                    'uid'            => $values['uid'],
                    'payment_status' => $values['payment_status'],
                    'start'          => $values['start'],
                    'end'            => $values['end'],
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
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
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
    }	

    public function updateAction()
    {
        // Set view
        $this->view()->setTemplate('invoice-update');
    }	
}
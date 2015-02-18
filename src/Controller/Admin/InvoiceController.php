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
use Zend\Json\Json;

class InvoiceController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $module = $this->params('module');
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $select = $this->getModel('invoice')->select()->order($order)->offset($offset)->limit($limit);
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
                'module'        => $this->getModule(),
                'controller'    => 'invoice',
                'action'        => 'index',
            )),
        ));
        // Set view
        $this->view()->setTemplate('invoice-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function viewAction()
    {
        // Get invoice
        $id = $this->params('id');
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Check invoice
        if (empty($invoice)) {
           $this->jump(array('', 'action' => 'index'), __('The invoice not found.'));
        }
        $invoice['time_create_view'] = _date($invoice['time_create']);
        $invoice['time_payment_view'] = ($invoice['time_payment']) ? _date($invoice['time_payment']) : __('Not yet');
        $invoice['amount_view'] = _currency($invoice['amount']);
        $invoice['log'] = Pi::api('log', 'order')->getLog($invoice['id']);
        // Set view
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
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

namespace Module\Shop\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\Shop\Form\OrderSettingForm;
use Module\Shop\Form\OrderSettingFilter;
use Module\Shop\Form\UpdateDeliveryForm;
use Module\Shop\Form\UpdateDeliveryFilter;
use Module\Shop\Form\UpdateOrderForm;
use Module\Shop\Form\UpdateOrderFilter;
use Module\Shop\Form\UpdatePaymentForm;
use Module\Shop\Form\UpdatePaymentFilter;

class OrderController extends ActionController
{
	public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $module = $this->params('module');
        $status_order = $this->params('status_order');
        $status_payment = $this->params('status_payment');
        $status_delivery = $this->params('status_delivery');
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $where = array();
        if ($status_order) {
            $where['status_order'] = $status_order;
        }
        if ($status_payment) {
            $where['status_payment'] = $status_payment;
        }
        if ($status_delivery) {
            $where['status_delivery'] = $status_delivery;
        }
        $select = $this->getModel('order')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('order')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = Pi::api('order', 'shop')->canonizeOrder($row);
        }
        // Set paginator
        $count = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = $this->getModel('order')->select()->columns($count)->where($where);
        $count = $this->getModel('order')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'          => $this->getModule(),
                'controller'      => 'order',
                'action'          => 'index',
                'status_order'    => $status_order,
                'status_payment'  => $status_payment,
                'status_delivery' => $status_delivery,
            )),
        ));
        // Set form
        $values = array(
            'status_order' => $status_order,
            'status_payment' => $status_payment,
            'status_delivery' => $status_delivery,
        );
        $form = new OrderSettingForm('setting');
        $form->setAttribute('action', $this->url('', array('action' => 'process')));
        $form->setData($values);
    	// Set view
    	$this->view()->setTemplate('order_index');
    	$this->view()->assign('list', $list);
    	$this->view()->assign('paginator', $paginator);
        $this->view()->assign('form', $form);
    }

    public function processAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new OrderSettingForm('setting');
            $form->setInputFilter(new OrderSettingFilter());
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $message = __('Go to filter');
                $url = array(
                    'action' => 'index',
                    'status_order' => $values['status_order'],
                    'status_payment' => $values['status_payment'],
                    'status_delivery' => $values['status_delivery'],
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

    public function updateOrderAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        $return = array();
        // Get order
        $order = $this->getModel('order')->find($id);
        // Set form
        $form = new UpdateOrderForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateOrderFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $order->status_order = $values['status_order'];
                if ($values['status_order'] == 7) {
                    $order->time_finish = time();
                } else {
                    $order->time_finish = 0;
                }
                $order->save();
                // Add log
                Pi::api('log', 'shop')->addLog('order', $order->id, 'update');
                // Set return
                $return['status'] = 1;
                $return['data'] = Pi::api('order', 'shop')->orderStatus($order->status_order);
                $return['data']['time_finish_view'] = ($order->time_finish) ? _date($order->time_finish) : __('Not Finish');
            } else {
                $return['status'] = 0;
                $return['data'] = '';
            }
            return $return; 
        } else {
            $values['status_order'] = $order->status_order;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', array('action' => 'updateOrder', 'id' => $order->id)));
        }    
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Update order'));
        $this->view()->assign('form', $form);
    }

    public function updatePaymentAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        $return = array();
        // Get order
        $order = $this->getModel('order')->find($id);
        // Set form
        $form = new UpdatePaymentForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdatePaymentFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $gateway = Pi::api('gateway', 'payment')->getGatewayInfo($values['payment_adapter'][0]);
                $order->status_payment = $values['status_payment'];
                $order->payment_adapter = $gateway['path'];
                $order->payment_method = $gateway['type'];
                if ($values['status_payment'] == 2) {
                    $order->time_payment = time();
                } else {
                    $order->time_payment = 0;
                }
                $order->save();
                // Add log
                Pi::api('log', 'shop')->addLog('payment', $order->id, 'update');
                // Set return
                $return['status'] = 1;
                $return['data'] = Pi::api('order', 'shop')->paymentStatus($order->status_payment);
                $return['data']['payment_adapter'] = $order->payment_adapter;
                $return['data']['payment_method'] = $order->payment_method;
                $return['data']['time_payment_view'] = ($order->time_payment) ? _date($order->time_payment) : __('Not Paid');
            } else {
                $return['status'] = 0;
                $return['data'] = '';
            }
            return $return; 
        } else {
            $values['status_payment'] = $order->status_payment;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', array('action' => 'updatePayment', 'id' => $order->id)));
        }    
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Update payment'));
        $this->view()->assign('form', $form);
    }

    public function updateDeliveryAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        $return = array();
        // Get order
        $order = $this->getModel('order')->find($id);
        // Set form
        $form = new UpdateDeliveryForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateDeliveryFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $order->status_delivery = $values['status_delivery'];
                if ($values['status_delivery'] != 1) {
                    $order->time_delivery = time();
                } else {
                    $order->time_delivery = 0;
                }
                $order->save();
                // Add log
                Pi::api('log', 'shop')->addLog('delivery', $order->id, 'update');
                // Set return
                $return['status'] = 1;
                $return['data'] = Pi::api('order', 'shop')->deliveryStatus($order->status_delivery);
                $return['data']['time_delivery_view'] = ($order->time_delivery) ? _date($order->time_delivery) : __('Not Delivery');
            } else {
                $return['status'] = 0;
                $return['data'] = '';
            }
            return $return; 
        } else {
            $values['status_delivery'] = $order->status_delivery;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', array('action' => 'updateDelivery', 'id' => $order->id)));
        }    
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Update delivery'));
        $this->view()->assign('form', $form);
    }

    public function viewAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        // Get order
        $order = $this->getModel('order')->find($id);
        $order = Pi::api('order', 'shop')->canonizeOrder($order);
        $order['product'] = Pi::api('order', 'shop')->listProduct($order['id']);
        // Add log
        Pi::api('log', 'shop')->addLog('order', $order['id'], 'view');
        // Set view
        $this->view()->setTemplate('order_view');
        $this->view()->assign('order', $order);
    }

    public function editAction()
    {
        $this->view()->setTemplate('empty');
    }

    public function printAction()
    {
        $this->view()->setTemplate('empty');
    }
}
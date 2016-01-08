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
use Module\Order\Form\OrderSettingForm;
use Module\Order\Form\OrderSettingFilter;
use Module\Order\Form\UpdateDeliveryForm;
use Module\Order\Form\UpdateDeliveryFilter;
use Module\Order\Form\UpdateOrderForm;
use Module\Order\Form\UpdateOrderFilter;
use Module\Order\Form\UpdatePaymentForm;
use Module\Order\Form\UpdatePaymentFilter;

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
        $code = $this->params('code');
        $mobile = $this->params('mobile');
        $email = $this->params('email');
        $city = $this->params('city');
        $uid = $this->params('uid');
        $id_number = $this->params('id_number');
        $first_name = $this->params('first_name');
        $last_name = $this->params('last_name');
        $zip_code = $this->params('zip_code');
        $company = $this->params('company');
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $where = array();
        // Set where
        if ($status_order) {
            $where['status_order'] = $status_order;
        }
        if ($status_payment) {
            $where['status_payment'] = $status_payment;
        }
        if ($status_delivery) {
            $where['status_delivery'] = $status_delivery;
        }
        if ($code) {
            $where['code LIKE ?'] = '%' . $code . '%';
        }
        if ($mobile) {
            $where['mobile LIKE ?'] = '%' . $mobile . '%';
        }
        if ($email) {
            $where['email LIKE ?'] = '%' . $email . '%';
        }
        if ($city) {
            $where['city LIKE ?'] = '%' . $city . '%';
        }
        if ($uid) {
            $where['uid'] = $uid;
        }
        if ($id_number) {
            $where['id_number LIKE ?'] = '%' . $id_number . '%';
        }
        if ($first_name) {
            $where['first_name LIKE ?'] = '%' . $first_name . '%';
        }
        if ($last_name) {
            $where['last_name LIKE ?'] = '%' . $last_name . '%';
        }
        if ($zip_code) {
            $where['zip_code LIKE ?'] = '%' . $zip_code . '%';
        }
        if ($company) {
            $where['company LIKE ?'] = '%' . $company . '%';
        }
        // Select
        $select = $this->getModel('order')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('order')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = Pi::api('order', 'order')->canonizeOrder($row);
        }
        // Set paginator
        $count = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = $this->getModel('order')->select()->columns($count)->where($where);
        $count = $this->getModel('order')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => $this->getModule(),
                'controller' => 'order',
                'action' => 'index',
                'status_order' => $status_order,
                'status_payment' => $status_payment,
                'status_delivery' => $status_delivery,
                'code' => $code,
                'mobile' => $mobile,
                'email' => $email,
                'city' => $city,
                'uid' => $uid,
                'id_number' => $id_number,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'zip_code' => $zip_code,
                'company' => $company,
            )),
        ));
        // Set form
        $values = array(
            'status_order' => $status_order,
            'status_payment' => $status_payment,
            'status_delivery' => $status_delivery,
            'code' => $code,
            'mobile' => $mobile,
            'email' => $email,
            'city' => $city,
            'uid' => $uid,
            'id_number' => $id_number,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'zip_code' => $zip_code,
            'company' => $company,
        );
        $form = new OrderSettingForm('setting');
        $form->setAttribute('action', $this->url('', array('action' => 'process')));
        $form->setData($values);
        // Set view
        $this->view()->setTemplate('order-index');
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
                    'code' => $values['code'],
                    'mobile' => $values['mobile'],
                    'email' => $values['email'],
                    'city' => $values['city'],
                    'uid' => $values['uid'],
                    'id_number' => $values['id_number'],
                    'first_name' => $values['first_name'],
                    'last_name' => $values['last_name'],
                    'zip_code' => $values['zip_code'],
                    'company' => $values['company'],
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
        if (in_array($order->status_order, array(4, 5, 6))) {
            $return['status'] = 0;
            $return['data'] = '';
            return $return;
        } else {
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
                    // Check order status
                    if (in_array($values['status_order'], array(4, 5, 6))) {
                        Pi::api('invoice', 'order')->cancelInvoiceFromOrder($order->toArray());
                    }
                    // Add log
                    //Pi::api('log', 'shop')->addLog('order', $order->id, 'update');
                    // Send notification
                    Pi::api('notification', 'order')->processOrder($order->toArray(), 'order');
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = Pi::api('order', 'order')->orderStatus($order->status_order);
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
                $gateway = Pi::api('gateway', 'order')->getGatewayInfo($values['gateway'][0]);
                $order->status_payment = $values['status_payment'];
                $order->gateway = $gateway['path'];
                if ($values['status_payment'] == 2) {
                    $order->time_payment = time();
                } else {
                    $order->time_payment = 0;
                }
                $order->save();
                // Add log
                //Pi::api('log', 'shop')->addLog('payment', $order->id, 'update');
                // Send notification
                Pi::api('notification', 'order')->processOrder($order->toArray(), 'payment');
                // Set return
                $return['status'] = 1;
                $return['data'] = Pi::api('order', 'order')->paymentStatus($order->status_payment);
                $return['data']['gateway'] = $order->gateway;
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
                //Pi::api('log', 'shop')->addLog('delivery', $order->id, 'update');
                // Send notification
                Pi::api('notification', 'order')->processOrder($order->toArray(), 'delivery');
                // Set return
                $return['status'] = 1;
                $return['data'] = Pi::api('order', 'order')->deliveryStatus($order->status_delivery);
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
        $order = Pi::api('order', 'order')->canonizeOrder($order);
        // set Products
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // set Products
        $order['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromOrder($order);
        // Add log
        //Pi::api('log', 'shop')->addLog('order', $order['id'], 'view');
        // Set view
        $this->view()->setTemplate('order-view');
        $this->view()->assign('order', $order);
    }

    public function editAction()
    {
        $this->view()->setTemplate('empty');
    }

    public function printAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        // Get order
        $order = $this->getModel('order')->find($id);
        $order = Pi::api('order', 'order')->canonizeOrder($order);
        // Set Products
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Set Products
        $order['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromOrder($order);
        // Set installment
        $order['installment'] = Pi::api('installment', 'order')->blockTable($order['user']);
        // Get all products
        $order['allproducts'] = Pi::api('order', 'order')->listAllProduct('shop');
        // Set view
        $this->view()->setTemplate('order-print')->setLayout('layout-content');
        $this->view()->assign('order', $order);
    }
}
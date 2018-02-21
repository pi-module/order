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
use Module\Order\Form\OrderAddFilter;
use Module\Order\Form\OrderAddForm;
use Module\Order\Form\OrderEditFilter;
use Module\Order\Form\OrderEditForm;
use Module\Order\Form\OrderProductFilter;
use Module\Order\Form\OrderProductForm;
use Module\Order\Form\OrderSettingFilter;
use Module\Order\Form\OrderSettingForm;
use Module\Order\Form\UpdateCanPayFilter;
use Module\Order\Form\UpdateCanPayForm;
use Module\Order\Form\UpdateDeliveryFilter;
use Module\Order\Form\UpdateDeliveryForm;
use Module\Order\Form\UpdateNoteFilter;
use Module\Order\Form\UpdateNoteForm;
use Module\Order\Form\UpdateOrderFilter;
use Module\Order\Form\UpdateOrderForm;
use Module\Order\Form\UpdatePaymentFilter;
use Module\Order\Form\UpdatePaymentForm;
use Zend\Db\Sql\Predicate\Expression;

class OrderController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $status_order = $this->params('status_order');
        $status_payment = $this->params('status_payment');
        $status_delivery = $this->params('status_delivery');
        $can_pay = $this->params('can_pay');
        $type_payment = $this->params('type_payment');
        $type_commodity = $this->params('type_commodity');
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
        $order = array('order.id DESC', 'time_create DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $where = array();
        // Set where
        if ($status_order) {
            $where['status_order'] = $status_order;
        } else {
            $where['status_order'] = array(1, 2, 3, 7);
        }
        if ($status_payment) {
            $where['status_payment'] = $status_payment;
        }
        if ($status_delivery) {
            $where['status_delivery'] = $status_delivery;
        }
        if ($can_pay) {
            $where['can_pay'] = $can_pay;
        }
        if (in_array($type_payment, array('free', 'onetime', 'recurring', 'installment'))) {
            $where['type_payment'] = $type_payment;
        }
        if (in_array($type_commodity, array('product', 'service'))) {
            $where['type_commodity'] = $type_commodity;
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
        $orderTable = Pi::model('order', 'order')->getTable();
        $orderAddressTable = Pi::model("order_address", 'order')->getTable();
     
        $select = Pi::db()->select();
        $select
        ->from(array('order' => $orderTable))
        ->join(array('order_address' => $orderAddressTable), 'order_address.order = order.id', array('id_number', 'first_name','last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company', 'company_id', 'company_vat', 'delivery', 'location'))
        ->where ($where)
        ->order ($order);
        
        $rowset = Pi::db()->query($select);
        
        // Make list
        foreach ($rowset as $row) {
            $invoiceList = Pi::api('invoice', 'order')->getInvoiceFromOrder($row['id'], false);
            $productList = Pi::api('order', 'order')->listProduct($row['id'], $row['module_name']);
            $list[$row['id']] = Pi::api('order', 'order')->canonizeOrder($row);
            $list[$row['id']]['products'] = $productList;
            $list[$row['id']]['invoiceList'] = $invoiceList;
            $list[$row['id']]['totalInvoice'] = 0;
            $list[$row['id']]['paidInvoice'] = 0;
            $list[$row['id']]['unPaidInvoice'] = 0;
            foreach ($invoiceList as $invoice) {

                echo '<pre>';
                print_r($invoice);
                echo '</pre>';

                $list[$row['id']]['totalInvoice']++;
                if ($invoice['status'] == 1) {
                    $list[$row['id']]['paidInvoice']++;
                } elseif ($invoice['status'] == 2) {
                    $list[$row['id']]['unPaidInvoice']++;
                }
            }
            if (!count($invoice)) {
                $list[$row['id']]['unPaidInvoice']++;
                $list[$row['id']]['statusInvoice'] = sprintf(
                    __('Total : %s / paid : %s / unPaid : %s'),
                    _number($list[$row['id']]['totalInvoice']),
                    _number($list[$row['id']]['paidInvoice']),
                    _number(0)
                );
            } else {
                $list[$row['id']]['statusInvoice'] = sprintf(
                    __('Total : %s / paid : %s / unPaid : %s'),
                    _number($list[$row['id']]['totalInvoice']),
                    _number($list[$row['id']]['paidInvoice']),
                    _number($list[$row['id']]['unPaidInvoice'])
                );
            }
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = Pi::db()->select();
        $select
        ->from(array('order' => $orderTable))->columns($count)
        ->join(array('order_address' => $orderAddressTable), 'order_address.order = order.id', array())
        ->where ($where)
        ->order ($order);
        $count = Pi::db()->query($select)->current()->count;
        
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
                'can_pay' => $can_pay,
                'type_payment' => $type_payment,
                'type_commodity' => $type_commodity,
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
            'can_pay' => $can_pay,
            'type_payment' => $type_payment,
            'type_commodity' => $type_commodity,
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
                    'can_pay' => $values['can_pay'],
                    'type_payment' => $values['type_payment'],
                    'type_commodity' => $values['type_commodity'],
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
                // Check status_payment
                if ($values['status_payment'] != $order->status_payment) {
                    switch ($values['status_payment']) {
                        // Set order is not pay
                        case 1:
                            $order->status_payment = 1;
                            $order->time_payment = 0;
                            // Update invoice
                            $this->getModel('invoice')->update(
                                array('status' => 2),
                                array('order' => $order->id)
                            );
                            break;

                        // Set order is pay
                        case 2:
                            $order->status_payment = 2;
                            $order->time_payment = time();
                            $order->gateway = 'Offline';
                            // Update invoice
                            $this->getModel('invoice')->update(
                                array('status' => 1, 'gateway' => 'Offline'),
                                array('order' => $order->id)
                            );
                            break;
                    }
                    $order->save();
                    // Send notification
                    Pi::api('notification', 'order')->processOrder($order->toArray(), 'payment');
                }
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

    public function updateCanPayAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        $return = array();
        // Get order
        $order = $this->getModel('order')->find($id);
        // Set form
        $form = new UpdateCanPayForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateCanPayFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Update order
                $order->can_pay = $values['can_pay'];
                $order->save();
                // Update invoice
                $this->getModel('invoice')->update(
                    array('can_pay' => $order->can_pay),
                    array('order' => $order->id)
                );
                // Send notification
                Pi::api('notification', 'order')->processOrderCanPay($order->toArray());
                // Set return
                $return['status'] = 1;
                $return['data'] = Pi::api('order', 'order')->canPayStatus($order->can_pay);
            } else {
                $return['status'] = 0;
                $return['data'] = '';
            }
            return $return;
        } else {
            $values['can_pay'] = $order->can_pay;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', array('action' => 'updateCanPay', 'id' => $order->id)));
        }
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Add / edit admin note'));
        $this->view()->assign('form', $form);
    }

    public function updateNoteAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        $return = array();
        // Get order
        $order = $this->getModel('order')->find($id);
        // Set form
        $form = new UpdateNoteForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateNoteFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $order->admin_note = $values['admin_note'];
                $order->save();
                // Send notification
                Pi::api('notification', 'order')->processOrderNote($order->toArray());
                // Set return
                $return['status'] = 1;
                $return['data']['admin_note'] = Pi::service('markup')->render($order->admin_note, 'html', 'text');
            } else {
                $return['status'] = 0;
                $return['data'] = '';
            }
            return $return;
        } else {
            $values['admin_note'] = $order->admin_note;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', array('action' => 'updateNote', 'id' => $order->id)));
        }
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Add / edit admin note'));
        $this->view()->assign('form', $form);
    }

    public function viewAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Get order
        $order = $this->getModel('order')->find($id);
        $order = Pi::api('order', 'order')->canonizeOrder($order);
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // set Products
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // set Products
        $order['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromOrder($order['id']);
        // Set status Invoice
        foreach ($order['invoices'] as $invoice) {
            $order['totalInvoice']++;
            if ($invoice['status'] == 1) {
                $order['paidInvoice']++;
            } elseif ($invoice['status'] == 2) {
                $order['unPaidInvoice']++;
            }
        }
        $order['statusInvoice'] = sprintf(
            __('Total : %s / paid : %s / unPaid : %s'),
            _number($order['totalInvoice']),
            _number($order['paidInvoice']),
            _number($order['unPaidInvoice'])
        );
        // credit
        if ($config['credit_active']) {
            $order['credit'] = Pi::api('credit', 'order')->getCredit($order['uid']);
        }
        // Add log
        //Pi::api('log', 'shop')->addLog('order', $order['id'], 'view');
        // Set view
        $this->view()->setTemplate('order-view');
        $this->view()->assign('order', $order);
        $this->view()->assign('address', $address);
        $this->view()->assign('config', $config);
    }

    public function addAction()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set option
        $option = array(
            'config' => $config,
        );
        // Set form
        $form = new OrderAddForm('addOrder', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderAddFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $values['ip'] = Pi::user()->getIp();
                $values['status_order'] = 1;
                $values['status_payment'] = 1;
                $values['status_delivery'] = 1;
                $values['can_pay'] = 1;
                // Get user
                $user = Pi::api('user', 'order')->getUserInformation($values['uid']);
                // Check user email
                if (!isset($values['email']) || empty($values['email'])) {
                    $values['email'] = $user['email'];
                }
                // Check user id_number
                if (!isset($values['id_number']) || empty($values['id_number'])) {
                    $values['id_number'] = $user['id_number'];
                }
                // Check user first_name
                if (!isset($values['first_name']) || empty($values['first_name'])) {
                    $values['first_name'] = $user['first_name'];
                }
                // Check user last_name
                if (!isset($values['last_name']) || empty($values['last_name'])) {
                    $values['last_name'] = $user['last_name'];
                }
                // Check user phone
                if (!isset($values['phone']) || empty($values['phone'])) {
                    $values['phone'] = $user['phone'];
                }
                // Check user mobile
                if (!isset($values['mobile']) || empty($values['mobile'])) {
                    $values['mobile'] = $user['mobile'];
                }
                // Check user address1
                if (!isset($values['address1']) || empty($values['address1'])) {
                    $values['address1'] = $user['address1'];
                }
                // Check user address2
                if (!isset($values['address2']) || empty($values['address2'])) {
                    $values['address2'] = $user['address2'];
                }
                // Check user country
                if (!isset($values['country']) || empty($values['country'])) {
                    $values['country'] = $user['country'];
                }
                // Check user state
                if (!isset($values['state']) || empty($values['state'])) {
                    $values['state'] = $user['state'];
                }
                // Check user city
                if (!isset($values['city']) || empty($values['city'])) {
                    $values['city'] = $user['city'];
                }
                // Check user zip_code
                if (!isset($values['zip_code']) || empty($values['zip_code'])) {
                    $values['zip_code'] = $user['zip_code'];
                }
                // Check user company
                if (!isset($values['company']) || empty($values['company'])) {
                    $values['company'] = $user['company'];
                }
                // Check user company_id
                if (!isset($values['company_id']) || empty($values['company_id'])) {
                    $values['company_id'] = $user['company_id'];
                }
                // Check user company_vat
                if (!isset($values['company_vat']) || empty($values['company_vat'])) {
                    $values['company_vat'] = $user['company_vat'];
                }
                // Check time create
                if (isset($values['time_create']) && !empty($values['time_create'])) {
                    $values['time_create'] = strtotime($values['time_create']);
                } else {
                    $values['time_create'] = time();
                }
                // Check user company_vat
                switch ($values['module_name']) {
                    case 'order';
                        $values['module_table'] = 'manual';
                        $values['module_item'] = 1;
                        break;

                    case 'shop';
                        $values['module_table'] = 'product';
                        $values['module_item'] = intval($values['module_item']);
                        break;

                    case 'guide';
                        $values['module_table'] = 'package';
                        $values['module_item'] = intval($values['module_item']);
                        break;

                    case 'plans';
                        $values['module_table'] = 'plans';
                        $values['module_item'] = intval($values['module_item']);
                        break;

                    case 'event';
                        $values['module_table'] = 'event';
                        $values['module_item'] = intval($values['module_item']);
                        break;
                }
                // Set additional price
                if ($values['type_commodity'] == 'product' && $config['order_additional_price_product'] > 0) {
                    $values['shipping_price'] = $values['shipping_price'] + $config['order_additional_price_product'];
                } elseif ($values['type_commodity'] == 'service' && $config['order_additional_price_service'] > 0) {
                    $values['setup_price'] = $values['setup_price'] + $config['order_additional_price_service'];
                }
                // Set total
                $values['total_price'] = $values['product_price'] + $values['shipping_price'] + $values['packing_price'] + $values['setup_price'] + $values['vat_price'];
                // Save values to order
                $order = $this->getModel('order')->createRow();
                $values['code'] = Pi::api('order', 'order')->generatCode();
                $order->assign($values);
                $order->save();
                
                // Save address
                $orderAddress = $this->getModel('order_address')->createRow();
                $columns = array('first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company', 'company_id', 'company_vat', 'delivery', 'location');
                foreach ($values as $key => $value) {
                    if (!in_array($key, $columns)) {
                        unset($values[$key]);
                    }
                }
                $values['order'] = $order->id;
                $values['type'] = 'INVOICING';
                $orderAddress->assign($values);
                $orderAddress->save();

                // Save basket
                $basket = $this->getModel('basket')->createRow();
                $basket->order = $order->id;
                $basket->product = $order->module_item;
                $basket->discount_price = $order->discount_price;
                $basket->shipping_price = $order->shipping_price;
                $basket->setup_price = $order->setup_price;
                $basket->vat_price = $order->vat_price;
                $basket->product_price = $order->product_price;
                $basket->total_price = $order->total_price;
                $basket->number = 1;
                $basket->save();

                // Set invoice
                Pi::api('invoice', 'order')->createInvoice($order->id, $order->uid);
                // Jump
                $message = __('New order added and data saved successfully.');
                $url = array('controller' => 'order', 'action' => 'view', 'id' => $order->id);
                $this->jump($url, $message);
            }
        }

        // Set view
        $this->view()->setTemplate('order-add');
        $this->view()->assign('form', $form);
    }

    public function editAction()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get id
        $id = $this->params('id');
        // Set option
        $option = array(
            'config' => $config,
        );
        // Set form
        $form = new OrderEditForm('editOrder', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderEditFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Save order
                $order = $this->getModel('order')->find($id);
                $order->assign($values);
                $order->save();
                // Check it save or not
                $message = __('Order information saved successfully.');
                $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order->id), $message);
            }
        } else {
            // Get order
            $order = Pi::api('order', 'order')->getOrder($id);
            $form->setData($order);
        }
        // Set view
        $this->view()->setTemplate('order-edit');
        $this->view()->assign('form', $form);
    }

    public function printAction()
    {
        // Get id
        $id = $this->params('id');
        // Get order
        $order = $this->getModel('order')->find($id);
        $order = Pi::api('order', 'order')->canonizeOrder($order);
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // Set Products
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        // Set Products
        $order['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromOrder($order['id']);
        // Set installment
        $order['installment'] = Pi::api('installment', 'order')->blockTable($order['user']);
        // Get all products
        $order['allproducts'] = Pi::api('order', 'order')->listAllProduct('shop');
        // Set view
        $this->view()->setTemplate('order-print')->setLayout('layout-content');
        $this->view()->assign('order', $order);
        $this->view()->assign('address', $address);
    }

    public function productAction()
    {
        // Set option
        $option = array();
        // Get id
        $order = $this->params('order');
        $order = Pi::api('order', 'order')->getOrder($order);
        // Check module
        if (in_array($order['module_name'], array('shop'/* ,'order', 'guide', 'plans'*/))) {
            switch ($order['module_name']) {
                case 'order':
                    $order['moduleTitle'] = __('Order module');
                    break;

                case 'shop':
                    $order['moduleTitle'] = __('Shop module');
                    break;

                case 'guide':
                    $order['moduleTitle'] = __('Guide module');
                    break;

                case 'plans':
                    $order['moduleTitle'] = __('Plans module');
                    break;
            }
        } else {
            $message = __('This order not supported add manual product');
            $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order['id']), $message, 'error');
        }
        // Get
        $invoices = Pi::api('invoice', 'order')->getInvoiceFromOrder($order['id']);
        foreach ($invoices as $invoice) {
            if ($invoice['status'] == 2) {
                $option['invoice'][$invoice['id']] = sprintf(__('Invoice %s by %s price and %s due date'),
                    $invoice['code'],
                    $invoice['total_price_view'],
                    $invoice['time_duedate_view']
                );
            }
        }
        $option['invoice'][0] = __('Generate new invoice');
        // Set form
        $form = new OrderProductForm('product', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderProductFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Get product
                switch ($order['module_name']) {
                    case 'shop':
                        // Get product
                        $product = Pi::api('product', 'shop')->getProductLight($values['id']);
                        if (!$product || $product['status'] != 1) {
                            $message = __('Your selected product not active / exist');
                            $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order['id']), $message, 'error');
                        }
                        // Add to basket
                        $basket = $this->getModel('basket')->createRow();
                        $basket->order = $order['id'];
                        $basket->product = $product['id'];
                        $basket->product_price = $product['price'];
                        $basket->total_price = $product['price'];
                        $basket->number = 1;
                        $basket->save();
                        // Add to invoice
                        if ($values['invoice'] == 0) {
                            $invoice = array();
                            $invoice['product_price'] = $product['price'];
                            $invoice['total_price'] = $product['price'];
                            $invoice['time_duedate'] = time();
                            $invoice['random_id'] = time() + rand(100, 999);
                            $invoice['uid'] = $order['uid'];
                            $invoice['ip'] = Pi::user()->getIp();
                            $invoice['status'] = 2;
                            $invoice['time_create'] = time();
                            $invoice['order'] = $order['id'];
                            $invoice['gateway'] = $order['gateway'];
                            // Set extra
                            if ($order['type_payment'] == 'installment') {
                                $extra = array();
                                $extra['order']['type_payment'] = $order['type_payment'];
                                $extra['order']['type_commodity'] = $order['type_commodity'];
                                $extra['number'] = '';
                                $extra['type'] = 'additional';
                                $invoice['extra'] = json::encode($extra);
                            }
                            // Save invoice
                            $row = $this->getModel('invoice')->createRow();
                            $row->assign($invoice);
                            $row->save();
                        } else {
                            $row = $this->getModel('invoice')->find($values['invoice']);
                            $row->product_price = $row->product_price + $product['price'];
                            $row->total_price = $row->total_price + $product['price'];
                            $row->save();
                        }
                        // Update order price
                        $this->getModel('order')->update(
                            array(
                                'product_price' => $order['product_price'] + $product['price'],
                                'total_price' => $order['total_price'] + $product['price'],
                            ),
                            array('id' => $order['id'])
                        );
                        break;

                    default:
                        $message = __('This order not supported add manual product');
                        $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order['id']), $message, 'error');
                        break;
                }
                // Check it save or not
                $message = __('New product / service added to your order');
                $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $order['id']), $message);
            }
        }

        // Set view
        $this->view()->setTemplate('order-product');
        $this->view()->assign('order', $order);
        $this->view()->assign('invoices', $invoices);
        $this->view()->assign('form', $form);
    }
    
    public function listUserAction()
    {
        // Get id
        $uid = $this->params('uid');
        // Get user info
        $user = Pi::api('user', 'order')->getUserInformation($uid);
        // Get order
        $user['orders'] = Pi::api('order', 'order')->getOrderFromUser($user['id'], true);
        // Set order ids
        $orderIds = array();
        $orderInstallmentCount = 1;
        foreach ($user['orders'] as $order) {
            $orderIds[] = $order['id'];
            if ($order['type_payment'] == 'installment') {
                $orderInstallmentCount++;
            }
        }
        // Get invoice
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id'], true, $orderIds);
        // Table view
        $tableView = array();
        if ($orderInstallmentCount > 0) {
            $tableView = Pi::api('installment', 'order')->blockTable($user, $orderIds);
        }
        // Set view
        $this->view()->setTemplate('order-list-user');
        $this->view()->assign('user', $user);
        $this->view()->assign('tableView', $tableView);
        $this->view()->assign('orderInstallmentCount', $orderInstallmentCount);
    }

    public function printPdfAction()
    {
        $id = $this->params('id');
        $ret = Pi::api('order', 'order')->pdf($id, false);
        if (!$ret['status']) {
            $this->jump(array('', 'controller' => 'index', 'action' => 'index'), $ret['message']);
        }
        
    }

}

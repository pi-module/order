<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Controller\Admin;

use Module\Order\Form\OrderProductFilter;
use Module\Order\Form\OrderProductForm;
use Module\Order\Form\OrderSettingFilter;
use Module\Order\Form\OrderSettingForm;
use Module\Order\Form\OrderUpdateFilter;
use Module\Order\Form\OrderUpdateForm;
use Module\Order\Form\UpdateCanPayFilter;
use Module\Order\Form\UpdateCanPayForm;
use Module\Order\Form\UpdateDeliveryFilter;
use Module\Order\Form\UpdateDeliveryForm;
use Module\Order\Form\UpdateInvoiceFilter;
use Module\Order\Form\UpdateInvoiceForm;
use Module\Order\Form\UpdateNoteFilter;
use Module\Order\Form\UpdateNoteForm;
use Module\Order\Form\UpdateOrderStatusFilter;
use Module\Order\Form\UpdateOrderStatusForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Predicate\In;

class OrderController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page            = $this->params('page', 1);
        $status_order    = $this->params('status_order');
        $status_delivery = $this->params('status_delivery');
        $can_pay         = $this->params('can_pay');
        $code            = $this->params('code');
        $uid             = $this->params('uid');
        $type_payment    = $this->params('type_payment');
        $type_commodity  = $this->params('type_commodity');

        // address
        $mobile     = $this->params('mobile');
        $email      = $this->params('email');
        $city       = $this->params('city');
        $id_number  = $this->params('id_number');
        $first_name = $this->params('first_name');
        $last_name  = $this->params('last_name');
        $zip_code   = $this->params('zip_code');
        $company    = $this->params('company');

        // installment 
        $status_payment = $this->params('status_payment');

        // Get info
        $list   = [];
        $ordering  = ['order.id DESC', 'order.time_create DESC'];
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit  = intval($this->config('admin_perpage'));
        $where  = [];
        $having = [];

        // Set where
        if ($status_order) {
            $where['status_order'] = $status_order;
        }
        if ($status_payment) {
            $having['status_payment'] = $status_payment;
        }
        if ($status_delivery) {
            $where['status_delivery'] = $status_delivery;
        }
        if ($can_pay) {
            $where['order.can_pay'] = $can_pay;
        }
        if (in_array($type_payment, ['free', 'onetime', 'recurring', 'installment'])) {
            $where['type_payment'] = $type_payment;
        }
        if (in_array($type_commodity, ['product', 'service', 'booking'])) {
            $where['type_commodity'] = $type_commodity;
        }
        if ($code) {
            $where['order.code LIKE ?'] = '%' . $code . '%';
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
        $orderTable              = Pi::model('order', 'order')->getTable();
        $orderDetailTable        = Pi::model('detail', 'order')->getTable();
        $orderAddressTable       = Pi::model("order_address", 'order')->getTable();
        $invoiceTable            = Pi::model("invoice", 'order')->getTable();
        $invoiceInstallmentTable = Pi::model("invoice_installment", 'order')->getTable();
        $detailTable             = Pi::model("detail", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])
            ->join(
                ['invoice' => $invoiceTable], new Expression(
                'invoice.order = order.id AND invoice.type= "NORMAL" AND invoice.status = ' . \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED
            ), ['invoice' => 'id'], 'left'
            )
            ->group(new Expression('order.id DESC'))
            ->where(['order.time_create >= ' . mktime(0, 0, 0, 1, 1, date('Y'))])
            ->where(['order.status_order != ' . \Module\Order\Model\Order::STATUS_ORDER_DRAFT])
        ;

        $rowset       = Pi::db()->query($select);

        $totalOwnBilled  = 0;
        $totalOwnOrdered = 0;
        $totalThirdBilled  = 0;
        $totalThirdOrdered = 0;
        $orders = [];
        $orderIds = [];
        foreach ($rowset as $row) {
            $orders[$row['id']] = $row;
            $orderIds[] = $row['id'];
        }
        $orderIds[] = -1;

        $select = Pi::db()->select();
        $select->from(['order_detail' => $orderDetailTable])->where(['`order` IN (' . join(', ', $orderIds) . ')']);
        $rowset       = Pi::db()->query($select);

        foreach ($rowset as $row) {
            $order = $orders[$row['order']];
            $totalPrice = $row['product_price'] - $row['discount_price'] + $row['shipping_price'] + $row['packing_price'] + $row['setup_price'] + $row['vat_price'];

            if (!$totalPrice) {
                continue;
            }
            if (Pi::api('order', $row['module'])->showInInvoice($order, $row, true)) {
                $totalThirdOrdered += $totalPrice;
                $orders[$row['order']]['total_third_ordered'] += $totalPrice;
                if ($order['invoice']) {
                    $totalThirdBilled += $totalPrice;
                    $orders[$row['order']]['total_third_billed'] += $totalPrice;
                }
            }

            $orders[$row['order']]['total_ordered'] += $totalPrice;
            if (Pi::api('order', $row['module'])->showInInvoice($order, $row)) {
                $totalOwnOrdered += $totalPrice;
                $orders[$row['order']]['total_own_ordered'] += $totalPrice;
                if ($order['invoice']) {
                    $totalOwnBilled += $totalPrice;
                    $orders[$row['order']]['total_own_billed'] += $totalPrice;
                }
            }
        }

        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])
            ->join(
                ['order_address' => $orderAddressTable], 'order_address.order = order.id',
                ['id_number', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company',
                 'company_id', 'company_vat', 'delivery', 'location']
            )
            ->join(
                ['invoice' => $invoiceTable], new Expression(
                'invoice.order = order.id AND invoice.type= "NORMAL" AND invoice.status = ' . \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED
            ), [], 'left'
            )
            ->join(
                ['invoice_installment' => $invoiceInstallmentTable],
                new Expression('invoice_installment.invoice = invoice.id AND invoice_installment.time_duedate <' . time()),
                ['status_payment' => new Expression("MIN(status_payment)")], 'left'
            )
            ->group('order.id')
            ->where($where)
            ->having($having)
            ->order($ordering)
            ->limit($limit)
            ->offset($offset);
        $rowset = Pi::db()->query($select);

        foreach ($rowset as $row) {
            $list[$row['id']]             = Pi::api('order', 'order')->canonizeOrder($row);
            $list[$row['id']]['products'] = Pi::api('order', 'order')->listProduct($row['id'], ['order' => $row]);
            $list[$row['id']]['total_own_billed_view'] = Pi::api('api', 'order')->viewPrice($orders[$row['id']]['total_own_billed']);
            $list[$row['id']]['total_own_ordered_view'] = Pi::api('api', 'order')->viewPrice($orders[$row['id']]['total_own_ordered']);
            $list[$row['id']]['total_third_billed_view'] = Pi::api('api', 'order')->viewPrice($orders[$row['id']]['total_third_billed']);
            $list[$row['id']]['total_third_ordered_view'] = Pi::api('api', 'order')->viewPrice($orders[$row['id']]['total_third_ordered']);
            $list[$row['id']]['total_ordered_view'] = Pi::api('api', 'order')->viewPrice($orders[$row['id']]['total_ordered']);
        }
        //
        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])
            ->join(
                ['order_address' => $orderAddressTable], 'order_address.order = order.id',
                ['id_number', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company',
                 'company_id', 'company_vat', 'delivery', 'location']
            )
            ->join(
                ['invoice' => $invoiceTable], new Expression(
                'invoice.order = order.id AND invoice.type= "NORMAL" AND invoice.status = ' . \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED
            ), [], 'left'
            )
            ->join(
                ['invoice_installment' => $invoiceInstallmentTable], 'invoice_installment.invoice = invoice.id',
                ['status_payment' => new Expression("MIN(status_payment)")], 'left'
            )
            ->group('order.id')
            ->where($where)
            ->having($having)
            ->order($ordering);
        $count = Pi::db()->query($select)->count();

        // Set paginator
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(
            [
                'router' => $this->getEvent()->getRouter(),
                'route'  => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
                'params' => array_filter(
                    [
                        'module'          => $this->getModule(),
                        'controller'      => 'order',
                        'action'          => 'index',
                        'status_order'    => $status_order,
                        'status_payment'  => $status_payment,
                        'status_delivery' => $status_delivery,
                        'can_pay'         => $can_pay,
                        'type_payment'    => $type_payment,
                        'type_commodity'  => $type_commodity,
                        'code'            => $code,
                        'mobile'          => $mobile,
                        'email'           => $email,
                        'city'            => $city,
                        'uid'             => $uid,
                        'id_number'       => $id_number,
                        'first_name'      => $first_name,
                        'last_name'       => $last_name,
                        'zip_code'        => $zip_code,
                        'company'         => $company,
                    ]
                ),
            ]
        );
        // Set form
        $values = [
            'status_order'    => $status_order,
            'status_payment'  => $status_payment,
            'status_delivery' => $status_delivery,
            'can_pay'         => $can_pay,
            'type_payment'    => $type_payment,
            'type_commodity'  => $type_commodity,
            'code'            => $code,
            'mobile'          => $mobile,
            'email'           => $email,
            'city'            => $city,
            'uid'             => $uid,
            'id_number'       => $id_number,
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'zip_code'        => $zip_code,
            'company'         => $company,
        ];
        $form   = new OrderSettingForm('setting');
        $form->setAttribute('action', $this->url('', ['action' => 'process']));
        $form->setData($values);
        // Set view
        $this->view()->setTemplate('order-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
        $this->view()->assign('form', $form);

        $this->view()->assign('totalOwnOrdered', $totalOwnOrdered);
        $this->view()->assign('totalOwnBilled', $totalOwnBilled);
        $this->view()->assign('totalThirdOrdered', $totalThirdOrdered);
        $this->view()->assign('totalThirdBilled', $totalThirdBilled);
    }

    public function processAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new OrderSettingForm('setting');
            $form->setInputFilter(new OrderSettingFilter());
            $form->setData($data);
            if ($form->isValid()) {
                $values  = $form->getData();
                $message = __('Filtered list');
                $url     = [
                    'action'          => 'index',
                    'status_order'    => $values['status_order'],
                    'status_payment'  => $values['status_payment'],
                    'status_delivery' => $values['status_delivery'],
                    'can_pay'         => $values['can_pay'],
                    'type_payment'    => $values['type_payment'],
                    'type_commodity'  => $values['type_commodity'],
                    'code'            => $values['code'],
                    'mobile'          => $values['mobile'],
                    'email'           => $values['email'],
                    'city'            => $values['city'],
                    'uid'             => $values['uid'],
                    'id_number'       => $values['id_number'],
                    'first_name'      => $values['first_name'],
                    'last_name'       => $values['last_name'],
                    'zip_code'        => $values['zip_code'],
                    'company'         => $values['company'],
                ];
            } else {
                $message = __('Not valid');
                $url     = [
                    'action' => 'index',
                ];
            }
        } else {
            $message = __('Not set');
            $url     = [
                'action' => 'index',
            ];
        }
        return $this->jump($url, $message);
    }

    public function updateOrderAction()
    {
        $id     = $this->params('id');
        $module = $this->params('module');

        $return = [];

        $order   = $this->getModel('order')->find($id);
        $options = [
            'has_valid_invoice' => Pi::api('order', 'order')->hasValidInvoice($order['id']),
        ];
        // Set form
        $form = new UpdateOrderStatusForm('updateOrder', $options);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateOrderStatusFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values              = $form->getData();
                $order->status_order = $values['status_order'];
                $order->save();

                Pi::api('notification', 'order')->processOrder($order->toArray(), 'order');

                // Set return
                $return['status'] = 1;
                $return['data']   = Pi::api('order', 'order')->orderStatus($order->status_order);
            } else {
                $return['status'] = 0;
                $return['data']   = '';
            }
            return $return;
        } else {
            $values['status_order'] = $order->status_order;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', ['action' => 'updateOrder', 'id' => $order->id]));
        }

        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Update order'));
        $this->view()->assign('form', $form);
    }

    public function updateInvoiceAction()
    {
        // Get id
        $id     = $this->params('id');
        $module = $this->params('module');
        $return = [];
        // Get order 
        $invoice = $this->getModel('invoice')->find($id);
        if ($invoice['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED || $invoice['type'] == 'CREDIT') {
            $return['status'] = 0;
            $return['data']   = '';
            return $return;
        }

        // Set form
        $options = [
            'status' => $invoice['status'],
        ];

        $form = new UpdateInvoiceForm('updateInvoice', $options);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateInvoiceFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Check status_payment
                if ($values['status'] != $invoice->status) {
                    if ($invoice->status == \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED
                        && $values['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED
                    ) {
                        Pi::api('invoice', 'order')->generateCreditInvoice($invoice);
                        $invoice->time_cancel = time();
                    }
                    $invoice->status = $values['status'];
                    $invoice->save();

                    if ($values['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED) {

                        Pi::api('invoice', 'order')->createInstallments($invoice->toArray());

                    }

                    // Send notification
                    $values = Pi::api('order', 'order')->getOrder($invoice['order']);
                    Pi::api('notification', 'order')->processOrder($values, 'payment');
                }
                // Set return
                $return['status'] = 1;
                $return['data']   = Pi::api('order', 'order')->invoiceStatus($invoice->status);
            } else {
                $return['status'] = 0;
                $return['data']   = '';
            }
            return $return;
        } else {
            $values['status'] = $invoice->status;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', ['action' => 'updateInvoice', 'id' => $invoice->id]));
        }
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Update invoice'));
        $this->view()->assign('form', $form);
    }

    public function updateDeliveryAction()
    {
        $id     = $this->params('id');
        $module = $this->params('module');

        $order = $this->getModel('order')->find($id);

        $form = new UpdateDeliveryForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateDeliveryFilter);
            $form->setData($data);
            $return = [];
            if ($form->isValid()) {
                $values                 = $form->getData();
                $order->status_delivery = $values['status_delivery'];
                if ($values['status_delivery'] != 1) {
                    $order->time_delivery = time();
                } else {
                    $order->time_delivery = 0;
                }
                $order->save();

                Pi::api('notification', 'order')->processOrder($order->toArray(), 'delivery');

                $return['status']                     = 1;
                $return['data']                       = Pi::api('order', 'order')->deliveryStatus($order->status_delivery);
                $return['data']['time_delivery_view'] = ($order->time_delivery) ? _date($order->time_delivery) : __('Not Delivery');
            } else {
                $return['status'] = 0;
                $return['data']   = '';
            }
            return $return;
        } else {
            $values['status_delivery'] = $order->status_delivery;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', ['action' => 'updateDelivery', 'id' => $order->id]));
        }
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Update delivery'));
        $this->view()->assign('form', $form);
    }

    public function updateCanPayAction()
    {
        $id     = $this->params('id');
        $module = $this->params('module');

        $order = $this->getModel('order')->find($id);

        $form = new UpdateCanPayForm('updateOrder');

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateCanPayFilter);
            $form->setData($data);
            $return = [];
            if ($form->isValid()) {
                $values = $form->getData();
                // Update order
                $order->can_pay = $values['can_pay'];
                $order->save();
                // Update invoice
                $this->getModel('invoice')->update(
                    ['can_pay' => $order->can_pay],
                    ['order' => $order->id]
                );
                // Send notification
                Pi::api('notification', 'order')->processOrderCanPay($order->toArray());
                // Set return
                $return['status'] = 1;
                $return['data']   = Pi::api('order', 'order')->canPayStatus($order->can_pay);
            } else {
                $return['status'] = 0;
                $return['data']   = '';
            }
            return $return;
        } else {
            $values['can_pay'] = $order->can_pay;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', ['action' => 'updateCanPay', 'id' => $order->id]));
        }
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Can pay ?'));
        $this->view()->assign('form', $form);
    }

    public function updateNoteAction()
    {
        $id     = $this->params('id');
        $module = $this->params('module');

        $order = $this->getModel('order')->find($id);

        $form = new UpdateNoteForm('updateOrder');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new UpdateNoteFilter);
            $form->setData($data);
            $return = [];
            if ($form->isValid()) {
                $values            = $form->getData();
                $order->admin_note = $values['admin_note'];
                $order->save();
                // Send notification
                Pi::api('notification', 'order')->processOrderNote($order->toArray());
                // Set return
                $return['status']             = 1;
                $return['data']['admin_note'] = Pi::service('markup')->render($order->admin_note, 'html', 'text');
            } else {
                $return['status'] = 0;
                $return['data']   = '';
            }
            return $return;
        } else {
            $values['admin_note'] = $order->admin_note;
            $form->setData($values);
            $form->setAttribute('action', $this->url('', ['action' => 'updateNote', 'id' => $order->id]));
        }
        // Set view
        $this->view()->setTemplate('system:component/form-popup');
        $this->view()->assign('title', __('Add / edit admin note'));
        $this->view()->assign('form', $form);
    }

    public function viewAction()
    {
        $id     = $this->params('id');
        $module = $this->params('module');

        $config = Pi::service('registry')->config->read($module);

        $order = $this->getModel('order')->find($id);
        $order = Pi::api('order', 'order')->canonizeOrder($order);

        $addressInvoicing = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $addressDelivery  = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'DELIVERY');

        $order['products']           = Pi::api('order', 'order')->listProduct($order['id'], ['order' => $order]);
        $order['invoices']           = Pi::api('invoice', 'order')->getInvoiceFromOrder($order['id']);
        $offline                     = false;
        $order['totalInstallments']  = 0;
        $order['paidInstallments']   = 0;
        $order['unPaidInstallments'] = 0;
        // Get installments and count paid and unpaid payment 
        foreach ($order['invoices'] as &$invoice) {
            $installments            = Pi::api('installment', 'order')->getInstallmentsFromInvoice($invoice['id']);
            $invoice['installments'] = $installments;

            $installment = current($installments);
            if ($order['type_commodity'] == 'service' && $installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID) {
                $order['time_delivery_view'] = _date($installment['time_payment']);
            }

            foreach ($installments as $installment) {
                if (Pi::api('gateway', 'order')->getGateway($installment['gateway'])) {
                    if (Pi::api('gateway', 'order')->getGateway($installment['gateway'])->gatewayRow['type'] == 'offline') {
                        $offline = true;
                    }
                }

                $order['totalInstallments']++;
                if ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID) {
                    $order['paidInstallments']++;
                } elseif ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID) {
                    $order['unPaidInstallments']++;
                }

                if ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID
                    || ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID
                        && $installment['gateway'] == 'manual')
                ) {
                    $order['can_pay'] = false;
                }
            }
        }
        $order['statusInstallments'] = sprintf(
            __('Total : %s / paid : %s / unPaid : %s'),
            _number($order['totalInstallments']),
            _number($order['paidInstallments']),
            _number($order['unPaidInstallments'])
        );
        //

        // get total price
        $order['total_price'] = 0;
        foreach ($order['products'] as &$product) {
            $totalPrice             = $product['product_price'] + $product['shipping_price'] + $product['packing_price'] + $product['setup_price']
                + $product['vat_price'] - $product['discount_price'];
            $product['total_price'] = $totalPrice;
            $order['total_price']   += $totalPrice;
        }
        $order['total_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_price']);
        //

        // credit
        if ($config['credit_active']) {
            $order['credit'] = Pi::api('credit', 'order')->getCredit($order['uid']);
        }
        // Set view
        $this->view()->setTemplate('order-view');
        $this->view()->assign('gateways', Pi::api('gateway', 'order')->getAdminGatewayList());
        $this->view()->assign('gatewaysInfo', Pi::api('gateway', 'order')->getAllGatewayList());
        $this->view()->assign('order', $order);
        $this->view()->assign('addressDelivery', $addressDelivery);
        $this->view()->assign('addressInvoicing', $addressInvoicing);
        $this->view()->assign('config', $config);
        $this->view()->assign('hasValidInvoice', Pi::api('order', 'order')->hasValidInvoice($order['id']));
        $this->view()->assign('hasDraftInvoice', Pi::api('order', 'order')->hasDraftInvoice($order['id']));
        $this->view()->assign('offline', $offline);

    }

    public function addAction()
    {
        $config = Pi::service('registry')->config->read($this->getModule());
        $option = [
            'config' => $config,
            'mode'   => 'add',
        ];

        $form = new OrderUpdateForm('addOrder', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderUpdateFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values                    = $form->getData();
                $values['ip']              = Pi::user()->getIp();
                $values['status_order']    = \Module\Order\Model\Order::STATUS_ORDER_DRAFT;
                $values['status_delivery'] = 1;
                $values['can_pay']         = 1;

                // Check addresses
                $user       = Pi::api('user', 'order')->getUserInformation($values['uid']);
                $checkField = ['email', 'id_number', 'first_name', 'last_name', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city',
                               'zip_code', 'company', 'company_id', 'company_vat'];
                foreach ($checkField as $field) {
                    if (!isset($values['delivery_' . $field]) || empty($values['delivery_' . $field])) {
                        $values['delivery_' . $field] = $user[$field];
                    }
                    if (!isset($values['invoicing_' . $field]) || empty($values['invoicing_' . $field])) {
                        $values['invoicing_' . $field] = $user[$field];
                    }
                }

                $values['time_create'] = time();
                $values['time_order']  = strtotime($values['time_order']);


                // Save values to order
                $order                  = $this->getModel('order')->createRow();
                $values['status_order'] = \Module\Order\Model\Order::STATUS_ORDER_DRAFT;
                $values['code']         = Pi::api('order', 'order')->generatCode();
                $values['create_by']    = 'ADMIN';
                $order->assign($values);
                $order->save();

                // Save address
                $columns         = ['first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code',
                                    'company', 'company_id', 'company_vat', 'delivery', 'location'];
                $orderAddress    = $this->getModel('order_address')->createRow();
                $deliveryAddress = [];
                foreach ($columns as $column) {
                    if (array_key_exists('delivery_' . $column, $values)) {
                        $deliveryAddress[$column] = $values['delivery_' . $column];
                    }
                }
                $deliveryAddress['order'] = $order->id;
                $deliveryAddress['type']  = 'DELIVERY';
                $orderAddress->assign($deliveryAddress);
                $orderAddress->save();

                $orderAddress     = $this->getModel('order_address')->createRow();
                $invoicingAddress = [];
                foreach ($columns as $column) {
                    if (array_key_exists('invoicing_' . $column, $values)) {
                        $invoicingAddress[$column] = $values['invoicing_' . $column];
                    }
                }
                $invoicingAddress['order'] = $order->id;
                $invoicingAddress['type']  = 'INVOICING';
                $orderAddress->assign($invoicingAddress);
                $orderAddress->save();
                //

                // Jump
                $message = __('New order added and data saved successfully.');
                $url     = ['controller' => 'order', 'action' => 'view', 'id' => $order->id];
                $this->jump($url, $message);
            }
        }

        // Set view
        $this->view()->setTemplate('order-add');
        $this->view()->assign('form', $form);
    }

    public function deleteAction()
    {
        $id = $this->params('id');
        if (Pi::api('order', 'order')->hasValidInvoice($id)) {
            $message = __('There valid invoices for this order. You cannot edit it.');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $id], $message);
        }

        $order = $this->getModel('order')->find($id);
        if ($order['create_by'] != 'ADMIN') {
            $message = __('Order not created by admin. You cannot delete it.');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $id], $message);
        }

        $installments = Pi::api('installment', 'order')->getInstallmentsFromOrder($order['id']);
        foreach ($installments as $installment) {
            Pi::model('invoice_installment', 'order')->delete(['id' => $installment['id']]);
        }

        Pi::model('invoice', 'order')->delete(['order' => $order['id']]);
        Pi::model('order_address', 'order')->delete(['order' => $order['id']]);
        Pi::model('order', 'order')->delete(['id' => $order['id']]);

        $message = __('Order deleted');
        $this->jump(['controller' => 'order', 'action' => 'index'], $message);

    }

    public function editAction()
    {
        $id = $this->params('id');

        if (Pi::api('order', 'order')->hasValidInvoice($id)) {
            $message = __('There valid invoices for this order. You cannot edit it.');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $id], $message);
        }

        $config = Pi::service('registry')->config->read($this->getModule());
        $option = [
            'config' => $config,
            'mode'   => 'edit',
        ];

        $form = new OrderUpdateForm('editOrder', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderUpdateFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();

                $order                = $this->getModel('order')->find($id);
                $values['time_order'] = strtotime($values['time_order']);
                $order->assign($values);
                $order->save();

                // Save address
                $this->getModel('order_address')->delete(['order' => $order->id]);
                $columns         = ['first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code',
                                    'company', 'company_id', 'company_vat', 'delivery', 'location'];
                $orderAddress    = $this->getModel('order_address')->createRow();
                $deliveryAddress = [];
                foreach ($columns as $column) {
                    if (array_key_exists('delivery_' . $column, $values)) {
                        $deliveryAddress[$column] = $values['delivery_' . $column];
                    }
                }
                $deliveryAddress['order'] = $order->id;
                $deliveryAddress['type']  = 'DELIVERY';
                $orderAddress->assign($deliveryAddress);
                $orderAddress->save();

                $orderAddress     = $this->getModel('order_address')->createRow();
                $invoicingAddress = [];
                foreach ($columns as $column) {
                    if (array_key_exists('invoicing_' . $column, $values)) {
                        $invoicingAddress[$column] = $values['invoicing_' . $column];
                    }
                }
                $invoicingAddress['order'] = $order->id;
                $invoicingAddress['type']  = 'INVOICING';
                $orderAddress->assign($invoicingAddress);
                $orderAddress->save();
                //

                $message = __('Order information saved successfully.');
                $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order->id], $message);
            }
        } else {
            $values           = Pi::api('order', 'order')->getOrder($id);
            $addressInvoicing = Pi::api('orderAddress', 'order')->findOrderAddress($values['id'], 'INVOICING');
            foreach ($addressInvoicing as $key => $value) {
                $values['invoicing_' . $key] = $value;
            }
            $addressDelivery = Pi::api('orderAddress', 'order')->findOrderAddress($values['id'], 'DELIVERY');
            foreach ($addressDelivery as $key => $value) {
                $values['delivery_' . $key] = $value;
            }
            $form->setData($values);
        }

        $this->view()->setTemplate('order-edit');
        $this->view()->assign('form', $form);
    }

    public function printAction()
    {
        // Get id
        $id = $this->params('id');
        // Get order
        $order   = $this->getModel('order')->find($id);
        $order   = Pi::api('order', 'order')->canonizeOrder($order);
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');

        // Set Products
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id']);
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
        $option = [];
        // Get id
        $order = $this->params('order');
        $id    = $this->params('id');

        if (Pi::api('order', 'order')->hasValidInvoice($order)) {
            $message = __('There valid invoices for this order. You cannot edit it.');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order], $message);
        }

        if ($id) {
            $detail = $this->getModel('detail')->find($id);
        }

        $order = Pi::api('order', 'order')->getOrder($order);

        // Set form
        $form = new OrderProductForm('product', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderProductFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Get product
                if (!Pi::api('order', $values['module'])->checkProduct($values['product'], $values['product_type'])) {
                    $message = __('Your selected product not active / exist');
                    $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order['id']], $message, 'error');
                }

                // Add to detail
                if (!$id) {
                    $detail = $this->getModel('detail')->createRow();
                }
                $detail->order          = $order['id'];
                $detail->module         = $values['module'];
                $detail->product        = $values['product'];
                $detail->product_type   = $values['product_type'];
                $detail->discount_price = $values['discount_price'] ?: 0;
                $detail->shipping_price = $values['shipping_price'] ?: 0;
                $detail->packing_price  = $values['packing_price'] ?: 0;
                $detail->setup_price    = $values['setup_price'] ?: 0;
                $detail->vat_price      = $values['vat_price'] ?: 0;
                $detail->product_price  = $values['product_price'] ?: 0;
                $detail->time_start     = $values['time_start'] ? strtotime($values['time_start']) : 0;
                $detail->time_end       = $values['time_end'] ? strtotime($values['time_end']) : 0;
                $detail->number         = 1;
                $detail->time_create    = time();
                $detail->extra          = Pi::api('order', $values['module'])->createExtraDetailForProduct($values);
                $detail->admin_note     = $values['admin_note'];
                $detail->save();

                $this->updateOrderType($order['id']);
                // Check it save or not
                $message = __('New product / service added to your order');
                $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order['id']], $message);
            }
        } else {
            if ($id) {
                $data = $detail->toArray();

                $extra = json_decode($data['extra'], true);
                foreach ($extra as $key => $value) {
                    $data['extra_' . $key] = $value;
                }
                $data['module_item'] = $extra['item'];
                $data['time_start']  = date('Y-m-d', $data['time_start']);
                $data['time_end']    = date('Y-m-d', $data['time_end']);
                $form->setData($data);
            }

        }

        // Set view
        $this->view()->setTemplate('order-product');
        $this->view()->assign('order', $order);
        $this->view()->assign('form', $form);
    }

    public function productDeleteAction()
    {
        // Set option
        $option = [];
        // Get id
        $id     = $this->params('id');
        $detail = $this->getModel('detail')->find($id);
        if (Pi::api('order', 'order')->hasValidInvoice($detail->order)) {
            $message = __('There valid invoices for this order. You cannot edit it.');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $detail->order], $message);
        }

        $order = Pi::api('order', 'order')->getOrder($detail->order);
        Pi::model('detail', 'order')->delete(['id' => $id]);
        $this->updateOrderType($order['id']);

        $message = __('Product deleted');
        $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $detail->order], $message);

    }

    private function updateOrderType($order)
    {
        $order         = Pi::model('order', 'order')->find($order);
        $products      = Pi::api('order', 'order')->listProduct($order->id);
        $typeCommodity = 'service';
        foreach ($products as $product) {
            if ($product['module'] == 'shop') {
                $typeCommodity = 'product';
                break;
            }
        }
        if ($order->type_commodity != $typeCommodity) {
            $order->type_commodity = $typeCommodity;
            $order->save();
        }
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
        $orderIds              = [];
        $orderInstallmentCount = 1;

        // Get invoice
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id'], true, $orderIds);
        foreach ($user['invoices'] as $invoice) {
            if ($invoice['type_payment'] == 'installment') {
                $invoiceInstallmentCount++;
            }
        }
        // Table view
        $tableView = [];
        if ($invoiceInstallmentCount > 0) {
            $tableView = Pi::api('installment', 'order')->blockTable($user, $orderIds);
        }
        // Set view
        $this->view()->setTemplate('order-list-user');
        $this->view()->assign('user', $user);
        $this->view()->assign('tableView', $tableView);
        $this->view()->assign('invoiceInstallmentCount', $invoiceInstallmentCount);
    }

    public function printPdfAction()
    {
        $id  = $this->params('id');
        $ret = Pi::api('order', 'order')->pdf($id, false);
        if (!$ret['status']) {
            $this->jump(['', 'controller' => 'index', 'action' => 'index'], $ret['message']);
        }

    }

}

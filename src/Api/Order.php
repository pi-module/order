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

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Json\Json;
use Zend\Math\Rand;

/*
 * Pi::api('order', 'order')->getOrder($id);
 * Pi::api('order', 'order')->getOrderFromUser($uid, $compressed);
 * Pi::api('order', 'order')->generatCode($id);
 * Pi::api('order', 'order')->orderStatus($status);
 * Pi::api('order', 'order')->paymentStatus($status);
 * Pi::api('order', 'order')->deliveryStatus($status);
 * Pi::api('order', 'order')->canonizeOrder($order);
 * Pi::api('order', 'order')->listProduct($id, $module);
 * Pi::api('order', 'order')->listAllProduct($module);
 * Pi::api('order', 'order')->updateOrder($id, $invoice);
 * Pi::api('order', 'order')->setOrderInfo($order);
 * Pi::api('order', 'order')->getOrderInfo();
 * Pi::api('order', 'order')->updateOrderInfo($order);
 * Pi::api('order', 'order')->unsetOrderInfo();
 */

class Order extends AbstractApi
{
    public function getOrder($id)
    {
        $order = Pi::model('order', $this->getModule())->find($id);
        $order = $this->canonizeOrder($order);
        return $order;
    }

    public function getOrderFromUser($uid, $compressed = false)
    {
        $orders = array();
        // Check compressed
        if ($compressed) {
            $where = array('uid' => $uid, 'status_order' => array(1, 2, 3, 7));
        } else {
            $where = array('uid' => $uid);
        }
        $order = array('time_create DESC', 'id DESC');
        // Select
        $select = Pi::model('order', $this->getModule())->select()->where($where)->order($order);;
        $rowset = Pi::model('order', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $orders[$row->id] = $this->canonizeOrder($row);
        }
        return $orders;
    }

    public function generatCode($id)
    {
        $config = Pi::service('registry')->config->read($this->getModule());
        return sprintf('%s-%s', $config['order_code_prefix'], $id);
    }

    public function orderStatus($status)
    {
        $return = array();
        switch ($status) {
            case '1':
                $return['orderClass'] = 'btn-warning';
                $return['orderLabel'] = 'label-warning';
                $return['orderTableBg'] = 'warning';
                $return['orderBg'] = 'bg-warning';
                $return['orderTitle'] = __('Not processed');
                break;

            case '2':
                $return['orderClass'] = 'btn-success';
                $return['orderLabel'] = 'label-success';
                $return['orderTableBg'] = 'success';
                $return['orderBg'] = 'bg-success';
                $return['orderTitle'] = __('Validated');
                break;

            case '3':
                $return['orderClass'] = 'btn-danger';
                $return['orderLabel'] = 'label-danger';
                $return['orderTableBg'] = 'danger';
                $return['orderBg'] = 'bg-danger';
                $return['orderTitle'] = __('Pending');
                break;

            case '4':
                $return['orderClass'] = 'btn-danger';
                $return['orderLabel'] = 'label-danger';
                $return['orderTableBg'] = 'danger';
                $return['orderBg'] = 'bg-danger';
                $return['orderTitle'] = __('Orders failed');
                break;

            case '5':
                $return['orderClass'] = 'btn-danger';
                $return['orderLabel'] = 'label-danger';
                $return['orderTableBg'] = 'danger';
                $return['orderBg'] = 'bg-danger';
                $return['orderTitle'] = __('Cancelled');
                break;

            case '6':
                $return['orderClass'] = 'btn-danger';
                $return['orderLabel'] = 'label-danger';
                $return['orderTableBg'] = 'danger';
                $return['orderBg'] = 'bg-danger';
                $return['orderTitle'] = __('Fraudulent orders');
                break;

            case '7':
                $return['orderClass'] = 'btn-primary';
                $return['orderLabel'] = 'label-primary';
                $return['orderTableBg'] = 'info';
                $return['orderBg'] = 'bg-primary';
                $return['orderTitle'] = __('Finished');
                break;
        }
        return $return;
    }

    public function canPayStatus($status)
    {
        $return = array();
        switch ($status) {
            case '1':
                $return['canPayClass'] = 'btn-success';
                $return['canPayLabel'] = 'label-success';
                $return['canPayTitle'] = __('Can pay');
                break;

            case '2':
                $return['canPayClass'] = 'btn-warning';
                $return['canPayLabel'] = 'label-warning';
                $return['canPayTitle'] = __('Can not pay');
                break;
        }
        return $return;
    }

    public function paymentStatus($status, $type)
    {
        $return = array();
        switch ($status) {
            case '1':
                $return['paymentClass'] = 'btn-warning';
                $return['paymentLabel'] = 'label-warning';
                $return['paymentTitle'] = __('UnPaid');
                break;

            case '2':
                $return['paymentClass'] = 'btn-success';
                $return['paymentLabel'] = 'label-success';
                $return['paymentTitle'] = ($type == 'installment') ? __('Paid prepayment') : __('Paid');
                break;
        }
        return $return;
    }

    public function deliveryStatus($status)
    {
        $return = array();
        switch ($status) {
            case '1':
                $return['deliveryClass'] = 'btn-warning';
                $return['deliveryLabel'] = 'label-warning';
                $return['deliveryTitle'] = __('Not processed');
                break;

            case '2':
                $return['deliveryClass'] = 'btn-info';
                $return['deliveryLabel'] = 'label-info';
                $return['deliveryTitle'] = __('Packed');
                break;

            case '3':
                $return['deliveryClass'] = 'btn-info';
                $return['deliveryLabel'] = 'label-info';
                $return['deliveryTitle'] = __('Posted');
                break;

            case '4':
                $return['deliveryClass'] = 'btn-success';
                $return['deliveryLabel'] = 'label-success';
                $return['deliveryTitle'] = __('Delivered');
                break;

            case '5':
                $return['deliveryClass'] = 'btn-danger';
                $return['deliveryLabel'] = 'label-danger';
                $return['deliveryTitle'] = __('Back eaten');
                break;
        }
        return $return;
    }

    public function canonizeOrder($order)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set date_format
        $pattern = !empty($config['date_format']) ? $config['date_format'] : 'yyyy-MM-dd';
        // boject to array
        $order = $order->toArray();
        // Set time_create_view
        $order['time_create_view'] = _date($order['time_create'], array('pattern' => $pattern));
        // Set time_payment_view
        $order['time_payment_view'] = ($order['time_payment']) ? _date($order['time_payment'], array('pattern' => $pattern)) : __('Not Paid');
        // Set time_delivery_view
        $order['time_delivery_view'] = ($order['time_delivery']) ? _date($order['time_delivery'], array('pattern' => $pattern)) : __('Not Delivery');
        // Set time_finish_view
        $order['time_finish_view'] = ($order['time_finish']) ? _date($order['time_finish'], array('pattern' => $pattern)) : __('Not Finish');
        // Set time_finish_view
        $order['time_start_view'] = ($order['time_start']) ? _date($order['time_start'], array('pattern' => $pattern)) : __('Not Start');
        // Set time_finish_view
        $order['time_end_view'] = ($order['time_end']) ? _date($order['time_end'], array('pattern' => $pattern)) : __('Not End');
        // Set product_price_view
        $order['product_price_view'] = Pi::api('api', 'order')->viewPrice($order['product_price']);
        // Set discount_price_view
        $order['discount_price_view'] = Pi::api('api', 'order')->viewPrice($order['discount_price']);
        // Set shipping_price_view
        $order['shipping_price_view'] = Pi::api('api', 'order')->viewPrice($order['shipping_price']);
        // Set packing_price_view
        $order['packing_price_view'] = Pi::api('api', 'order')->viewPrice($order['packing_price']);
        // Set setup_price_view
        $order['setup_price_view'] = Pi::api('api', 'order')->viewPrice($order['setup_price']);
        // Set packing_price_view
        $order['vat_price_view'] = Pi::api('api', 'order')->viewPrice($order['vat_price']);
        // Set total_price_view
        $order['total_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_price']);
        // Set paid_price_view
        $order['paid_price_view'] = Pi::api('api', 'order')->viewPrice($order['paid_price']);
        // Set user
        $order['user'] = Pi::api('user', 'order')->getUserInformation($order['uid']);
        // Set url_update_order
        $order['url_update_order'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'updateOrder',
            'id' => $order['id'],
        )));
        // Set url_update_payment
        $order['url_update_payment'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'updatePayment',
            'id' => $order['id'],
        )));
        // Set url_update_delivery
        $order['url_update_delivery'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'updateDelivery',
            'id' => $order['id'],
        )));
        // Set url_update_delivery
        $order['url_update_canPay'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'updateCanPay',
            'id' => $order['id'],
        )));
        //
        $order['url_update_note'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'updateNote',
            'id' => $order['id'],
        )));
        // Set url_edit
        $order['url_edit'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'edit',
            'id' => $order['id'],
        )));
        // Set url_print
        $order['url_print'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'print',
            'id' => $order['id'],
        )));
        // Set url_view
        $order['url_view'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'view',
            'id' => $order['id'],
        )));
        // Set url_view
        $order['url_list_user'] = Pi::url(Pi::service('url')->assemble('admin', array(
            'controller' => 'order',
            'action' => 'listUser',
            'uid' => $order['uid'],
        )));
        // Status order
        $status_order = $this->orderStatus($order['status_order']);
        $order['orderClass'] = $status_order['orderClass'];
        $order['orderLabel'] = $status_order['orderLabel'];
        $order['orderTitle'] = $status_order['orderTitle'];
        $order['orderTableBg'] = $status_order['orderTableBg'];
        $order['orderBg'] = $status_order['orderBg'];
        // Status payment
        $status_payment = $this->paymentStatus($order['status_payment'], $order['type_payment']);
        $order['paymentClass'] = $status_payment['paymentClass'];
        $order['paymentLabel'] = $status_payment['paymentLabel'];
        $order['paymentTitle'] = $status_payment['paymentTitle'];
        // Status delivery
        $status_delivery = $this->deliveryStatus($order['status_delivery']);
        $order['deliveryClass'] = $status_delivery['deliveryClass'];
        $order['deliveryLabel'] = $status_delivery['deliveryLabel'];
        $order['deliveryTitle'] = $status_delivery['deliveryTitle'];
        //
        $can_pay = $this->canPayStatus($order['can_pay']);
        $order['canPayClass'] = $can_pay['canPayClass'];
        $order['canPayLabel'] = $can_pay['canPayLabel'];
        $order['canPayTitle'] = $can_pay['canPayTitle'];
        //
        if ($order['type_commodity'] == 'product') {
            $order['type_commodity_view'] = __('Product');
        } elseif ($order['type_commodity'] == 'service') {
            $order['type_commodity_view'] = __('Service');
        }
        //
        if (in_array($order['status_order'], array(1, 2, 3))) {
            if ($order['status_payment'] == 2) {
                if ($order['status_delivery'] == 1) {
                    $order['shortStatus'] = $order['paymentTitle'];
                    $order['shortLabel'] = $order['paymentLabel'];
                } else {
                    $order['shortStatus'] = $order['deliveryTitle'];
                    $order['shortLabel'] = $order['deliveryLabel'];
                }
            } else {
                $order['shortStatus'] = $order['paymentTitle'];
                $order['shortLabel'] = $order['paymentLabel'];
            }
        } else {
            $order['shortStatus'] = $order['orderTitle'];
            $order['shortLabel'] = $order['orderLabel'];
        }
        // Set text_summary
        $order['user_note'] = Pi::service('markup')->render($order['user_note'], 'html', 'text');
        // Set text_summary
        $order['admin_note'] = Pi::service('markup')->render($order['admin_note'], 'html', 'text');
        // return order
        return $order;
    }

    public function listProduct($id, $module)
    {
        $list = array();
        $where = array('order' => $id);
        $select = Pi::model('basket', $this->getModule())->select()->where($where);
        $rowset = Pi::model('basket', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['details'] = Pi::api('order', $module)->getProductDetails($row->product, $row->extra);
            if (empty($row->extra)) {
                $list[$row->id]['extra'] = array();
            } else {
                $list[$row->id]['extra'] = json::decode($row->extra, true);
                // Set template and view
                if (isset($list[$row->id]['extra']['view_type']) && $list[$row->id]['extra']['view_type'] == 'template') {
                    $list[$row->id]['extra']['view_type'] = 'template';
                    if (!isset($list[$row->id]['extra']['view_template']) || empty($list[$row->id]['extra']['view_template'])) {
                        $list[$row->id]['extra']['view_template'] = 'order-detail';
                    }
                } else {
                    $list[$row->id]['extra']['view_type'] = 'simple';
                    $list[$row->id]['extra']['view_template'] = '';
                }
                // Get detail
                if (isset($list[$row->id]['extra']['getDetail']) && $list[$row->id]['extra']['getDetail']) {
                    $list[$row->id]['extra']['orderDetail'] = Pi::api('order', $module)->getOrder($id, 'order');
                }
            }
        }
        return $list;
    }

    public function listAllProduct($module)
    {
        $list = array();
        $select = Pi::model('basket', $this->getModule())->select();
        $rowset = Pi::model('basket', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['details'] = Pi::api('order', $module)->getProductDetails($row->product, $row->extra);
            if (empty($row->extra)) {
                $list[$row->id]['extra'] = array();
            } else {
                $list[$row->id]['extra'] = json::decode($row->extra, true);
            }
        }
        return $list;
    }

    public function updateOrder($orderId, $invoiceId)
    {
        // Get order
        $order = Pi::model('order', $this->getModule())->find($orderId);
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($invoiceId);
        // Checl for installment
        if ($order->type_payment == 'installment') {
            if ($invoice['extra']['type'] == 'prepayment') {
                // Update order
                $order->time_payment = time();
                $order->status_payment = 2;
                $order->save();
                // Canonize order
                $order = $this->canonizeOrder($order);
                // Get order basket
                $basket = array();
                $where = array('order' => $orderId);
                $select = Pi::model('basket', $this->getModule())->select()->where($where);
                $rowset = Pi::model('basket', $this->getModule())->selectWith($select);
                foreach ($rowset as $row) {
                    $basket[$row->id] = $row->toArray();
                    if (empty($row->extra)) {
                        $basket[$row->id]['extra'] = array();
                    } else {
                        $basket[$row->id]['extra'] = json::decode($row->extra, true);
                    }
                }
                // Update module and get back url
                $backUrl = Pi::api('order', $order['module_name'])->postPaymentUpdate($order, $basket);
            }
        } else {
            // Update order
            $order->time_payment = time();
            $order->status_payment = 2;
            $order->save();
            // Canonize order
            $order = $this->canonizeOrder($order);
            // Get order basket
            $basket = array();
            $where = array('order' => $orderId);
            $select = Pi::model('basket', $this->getModule())->select()->where($where);
            $rowset = Pi::model('basket', $this->getModule())->selectWith($select);
            foreach ($rowset as $row) {
                $basket[$row->id] = $row->toArray();
                if (empty($row->extra)) {
                    $basket[$row->id]['extra'] = array();
                } else {
                    $basket[$row->id]['extra'] = json::decode($row->extra, true);
                }
            }
            // Update module and get back url
            $backUrl = Pi::api('order', $order['module_name'])->postPaymentUpdate($order, $basket);
            // Accept Order Credit
            Pi::api('credit', 'order')->acceptOrderCredit($orderId, $invoiceId);
        }
        // Get back url
        if (!isset($backUrl) || empty($backUrl)) {
            $backUrl = $invoice['order_url'];
        }

        return $backUrl;
    }

    public function setOrderInfo($order)
    {
        // Empty order
        if (isset($_SESSION['order'])) {
            unset($_SESSION['order']);
        }
        // Set order to session
        $_SESSION['order'] = $order;
        // Set checkout url
        if (isset($order['type_payment']) && $order['type_payment'] == 'installment') {
            $checkout = Pi::url(Pi::service('url')->assemble('order', array(
                'module' => 'order',
                'controller' => 'checkout',
                'action' => 'installment',
            )));
        } else {
            $checkout = Pi::url(Pi::service('url')->assemble('order', array(
                'module' => 'order',
                'controller' => 'checkout',
                'action' => 'index',
            )));
        }
        return $checkout;
    }

    public function getOrderInfo()
    {
        if (isset($_SESSION['order']) && !empty($_SESSION['order'])) {
            return $_SESSION['order'];
        }
        return '';
    }

    public function updateOrderInfo($data)
    {
        if (isset($data['plan'])) {
            $_SESSION['order']['plan'] = $data['plan'];
        }
    }

    public function unsetOrderInfo()
    {
        if (isset($_SESSION['order']) && !empty($_SESSION['order'])) {
            unset($_SESSION['order']);
        }
    }
}
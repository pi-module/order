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
        // Select
        $select = Pi::model('order', $this->getModule())->select()->where($where);
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
                $return['orderTitle'] = __('Not processed');
                break;

            case '2':
                $return['orderClass'] = 'btn-success';
                $return['orderTitle'] = __('Orders validated');
                break;

            case '3':
                $return['orderClass'] = 'btn-danger';
                $return['orderTitle'] = __('Orders pending');
                break;

            case '4':
                $return['orderClass'] = 'btn-danger';
                $return['orderTitle'] = __('Orders failed');
                break;

            case '5':
                $return['orderClass'] = 'btn-danger';
                $return['orderTitle'] = __('Orders cancelled');
                break;

            case '6':
                $return['orderClass'] = 'btn-danger';
                $return['orderTitle'] = __('Fraudulent orders');
                break;

            case '7':
                $return['orderClass'] = 'btn-primary';
                $return['orderTitle'] = __('Orders finished');
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
                $return['paymentTitle'] = __('UnPaid');
                break;

            case '2':
                $return['paymentClass'] = 'btn-success';
                $return['paymentTitle'] = ($type == 'installment') ? __('Paid perpayment') : __('Paid');
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
                $return['deliveryTitle'] = __('Not processed');
                break;

            case '2':
                $return['deliveryClass'] = 'btn-info';
                $return['deliveryTitle'] = __('Packed');
                break;

            case '3':
                $return['deliveryClass'] = 'btn-info';
                $return['deliveryTitle'] = __('Posted');
                break;

            case '4':
                $return['deliveryClass'] = 'btn-success';
                $return['deliveryTitle'] = __('Delivered');
                break;

            case '5':
                $return['deliveryClass'] = 'btn-danger';
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
        // Status order
        $status_order = $this->orderStatus($order['status_order']);
        $order['orderClass'] = $status_order['orderClass'];
        $order['orderTitle'] = $status_order['orderTitle'];
        // Status payment
        $status_payment = $this->paymentStatus($order['status_payment'], $order['type_payment']);
        $order['paymentClass'] = $status_payment['paymentClass'];
        $order['paymentTitle'] = $status_payment['paymentTitle'];
        // Status delivery
        $status_delivery = $this->deliveryStatus($order['status_delivery']);
        $order['deliveryClass'] = $status_delivery['deliveryClass'];
        $order['deliveryTitle'] = $status_delivery['deliveryTitle'];
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
            $list[$row->id]['details'] = Pi::api('order', $module)->getProductDetails($row->product);
            if (empty($row->extra)) {
                $list[$row->id]['extra'] = array();
            } else {
                $list[$row->id]['extra'] = json::decode($row->extra, true);
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
            $list[$row->id]['details'] = Pi::api('order', $module)->getProductDetails($row->product);
            if (empty($row->extra)) {
                $list[$row->id]['extra'] = array();
            } else {
                $list[$row->id]['extra'] = json::decode($row->extra, true);
            }
        }
        return $list;
    }

    public function updateOrder($id, $invoice)
    {
        // Get order
        $order = Pi::model('order', $this->getModule())->find($id);
        // Checl for installment
        if ($order->type_payment == 'installment') {
            $invoice = Pi::api('invoice', 'order')->getInvoice($invoice);
            if ($invoice['extra']['type'] == 'prepayment') {
                // Update order
                $order->time_payment = time();
                $order->status_payment = 2;
                $order->save();
                // Canonize order
                $order = $this->canonizeOrder($order);
                // Get order basket
                $basket = array();
                $where = array('order' => $id);
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
            } else {
                // Get back url
                $backUrl = $invoice['order_url'];
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
            $where = array('order' => $id);
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
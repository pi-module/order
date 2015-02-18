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
 * Pi::api('order', 'order')->checkoutConfig();
 * Pi::api('order', 'order')->generatCode();
 * Pi::api('order', 'order')->viewPrice($price);
 * Pi::api('order', 'order')->orderStatus($status);
 * Pi::api('order', 'order')->paymentStatus($status);
 * Pi::api('order', 'order')->deliveryStatus($status);
 * Pi::api('order', 'order')->canonizeOrder($order);
 * Pi::api('order', 'order')->listProduct($order);
 * Pi::api('order', 'order')->setOrder($order);
 */

class Order extends AbstractApi
{
    public function getOrder($id)
    {
        $order = Pi::model('order', $this->getModule())->find($id);
        $order = $this->canonizeOrder($order);
        return $order;
    }

    public function checkoutConfig()
    {
        $return = array();
        // Set location
        $select = Pi::model('location', 'order')->select();
        $location = Pi::model('location', 'order')->selectWith($select)->toArray();
        $return['location'] = (empty($location)) ? 0 : 1;
        // Set delivery
        $select = Pi::model('delivery', 'order')->select();
        $delivery = Pi::model('delivery', 'order')->selectWith($select)->toArray();
        $return['delivery'] = (empty($delivery)) ? 0 : 1;
        // Set gateway
        $gateway = Pi::api('gateway', 'order')->getActiveGatewayList();
        $return['gateway'] = (empty($gateway)) ? 0 : 1;
        // return
        return $return;
    }

    public function generatCode()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        $prefix = $config['order_code_prefix'];
        // Generate random code
        $rand = Rand::getInteger(10000000, 99999999);
        // Generate order code
        $code = sprintf('%s-%s', $prefix, $rand);
        return $code;
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
                $return['orderClass'] = 'btn-inverse';
                $return['orderTitle'] = __('Orders finished');
                break;    
        }
        return $return;
    }

    public function paymentStatus($status)
    {
        $return = array();
        switch ($status) {
            case '1':
                $return['paymentClass'] = 'btn-warning';
                $return['paymentTitle'] = __('UnPaid');
                break;

            case '2':
                $return['paymentClass'] = 'btn-success';
                $return['paymentTitle'] = __('Paid');
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

    public function viewPrice($price)
    {
        if ($price > 0) {
            $viewPrice = _currency($price);
        } else {
            $viewPrice = 0;
        }
        return $viewPrice;

    }

    public function canonizeOrder($order)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // boject to array
        $order = $order->toArray();
        // Set time_create_view
        $order['time_create_view'] = _date($order['time_create']);
        // Set time_payment_view
        $order['time_payment_view'] = ($order['time_payment']) ? _date($order['time_payment']) : __('Not Paid');
        // Set time_delivery_view
        $order['time_delivery_view'] = ($order['time_delivery']) ? _date($order['time_delivery']) : __('Not Delivery');
        // Set time_finish_view
        $order['time_finish_view'] = ($order['time_finish']) ? _date($order['time_finish']) : __('Not Finish');
        // Set product_price_view
        $order['product_price_view'] = $this->viewPrice($order['product_price']);
        // Set discount_price_view
        $order['discount_price_view'] = $this->viewPrice($order['discount_price']);
        // Set shipping_price_view
        $order['shipping_price_view'] = $this->viewPrice($order['shipping_price']);
        // Set packing_price_view
        $order['packing_price_view'] = $this->viewPrice($order['packing_price']);
        // Set total_price_view
        $order['total_price_view'] = $this->viewPrice($order['total_price']);
        // Set paid_price_view
        $order['paid_price_view'] = $this->viewPrice($order['paid_price']);
        // Set user
        $order['user'] = Pi::user()->get($order['uid'], array('id', 'identity', 'name', 'email'));
        // Set url_update_order
        $order['url_update_order'] = Pi::url(Pi::service('url')->assemble('', array(
            'controller'    => 'order',
            'action'        => 'updateOrder',
            'id'            => $order['id'],
        )));
        // Set url_update_payment
        $order['url_update_payment'] = Pi::url(Pi::service('url')->assemble('', array(
            'controller'    => 'order',
            'action'        => 'updatePayment',
            'id'            => $order['id'],
        )));
        // Set url_update_delivery
        $order['url_update_delivery'] = Pi::url(Pi::service('url')->assemble('', array(
            'controller'    => 'order',
            'action'        => 'updateDelivery',
            'id'            => $order['id'],
        )));
        // Set url_edit
        $order['url_edit'] = Pi::url(Pi::service('url')->assemble('', array(
            'controller'    => 'order',
            'action'        => 'edit',
            'id'            => $order['id'],
        )));
        // Set url_print
        $order['url_print'] = Pi::url(Pi::service('url')->assemble('', array(
            'controller'    => 'order',
            'action'        => 'print',
            'id'            => $order['id'],
        )));
        // Set url_view
        $order['url_view'] = Pi::url(Pi::service('url')->assemble('', array(
            'controller'    => 'order',
            'action'        => 'view',
            'id'            => $order['id'],
        )));
        // Status order
        $status_order = $this->orderStatus($order['status_order']);
        $order['orderClass'] = $status_order['orderClass'];
        $order['orderTitle'] = $status_order['orderTitle'];
        // Status payment
        $status_payment = $this->paymentStatus($order['status_payment']);
        $order['paymentClass'] = $status_payment['paymentClass'];
        $order['paymentTitle'] = $status_payment['paymentTitle'];
        // Status delivery
        $status_delivery = $this->deliveryStatus($order['status_delivery']);
        $order['deliveryClass'] = $status_delivery['deliveryClass'];
        $order['deliveryTitle'] = $status_delivery['deliveryTitle'];
        // return order
        return $order; 
    }

    public function listProduct($order)
    {

    }

    public function setOrder($order)
    {
        // Empty order
        if (isset($_SESSION['order'])) {
            unset($_SESSION['order']);
        }
        // Set order to session
        $_SESSION['order'] = $order;
        // Set checkout url
        $checkout = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => 'order',
            'controller'    => 'checkout',
            'action'        => 'index',
        )));
        return $checkout;
    }
}	
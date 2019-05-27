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

namespace Module\Order\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class DetailController extends IndexController
{
    public function indexAction()
    {
        // Check user
        $this->checkUser();

        $id     = $this->params('id');
        $module = $this->params('module');
        $config = Pi::service('registry')->config->read($module);

        $order = $this->getModel('order')->find($id);
        $order = Pi::api('order', 'order')->canonizeOrder($order);

        // Check order is for this user
        if ($order['uid'] != Pi::user()->getId()) {
            $this->jump(['', 'controller' => 'index', 'action' => 'index'], __('This is not your order.'));
        }
        // Check order is for this user
        if ($order['status_order'] == \Module\Order\Model\Order::STATUS_ORDER_DRAFT) {
            $this->jump(['', 'controller' => 'index', 'action' => 'index'], __('This order not active.'));
        }

        $order['has_payment'] = Pi::api('order', 'order')->hasPayment($order['id']);

        $addressInvoicing = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $addressDelivery  = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'DELIVERY');

        $order['products']           = Pi::api('order', 'order')->listProduct($order['id']);
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
        $this->view()->setTemplate('detail');
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

    public function printAction()
    {
        // Check user
        $this->checkUser();

        $id  = $this->params('id');
        $ret = Pi::api('invoice', 'order')->pdf($id);
        if (!$ret['status']) {
            $this->jump(['', 'controller' => 'index', 'action' => 'index'], $ret['message']);
        }
    }

}
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

namespace Module\Order\Block;

use Pi;

class Block
{
    public static function detail($options = [], $module = null)
    {
        // Set options
        $block = [];
        $block = array_merge($block, $options);
        // Get user info
        $user             = Pi::api('user', 'order')->getUserInformation();
        $user['orders']   = Pi::api('order', 'order')->getOrderFromUser($user['id']);
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id']);
        foreach ($user['invoices'] as &$invoice) {
            $products                = Pi::api('order', 'order')->listProduct($invoice['order']);
            $invoice['installments'] = Pi::api('installment', 'order')->getInstallmentsFromInvoice($invoice['id']);
            $totalPrice              = 0;
            foreach ($products as $product) {
                $totalPrice                                            = $product['product_price'] + $product['shipping_price'] + $product['packing_price']
                    + $product['setup_price'] + $product['vat_price'] - $product['discount_price'];
                $invoice['total_price']                                = $totalPrice;
                $invoice['total_price_view']                           = Pi::api('api', 'order')->viewPrice($totalPrice);
                $user['orders'][$invoice['order']]['total_price']      = $totalPrice;
                $user['orders'][$invoice['order']]['total_price_view'] = Pi::api('api', 'order')->viewPrice($totalPrice);
            }
        }
        // Set more link
        $block['more'] = Pi::url('order');
        // Set block array
        $block['resources'] = $user;
        $block['gateways']  = Pi::api('gateway', 'order')->getAdminGatewayList();
        return $block;
    }

    public static function installment($options = [], $module = null)
    {
        // Set options
        $block = [];
        $block = array_merge($block, $options);
        // Get user info
        $user = Pi::api('user', 'order')->getUserInformation();
        // Get order
        $user['orders'] = Pi::api('order', 'order')->getOrderFromUser($user['id'], true);
        // Set order ids
        $orderIds = [];
        foreach ($user['orders'] as $order) {
            $orderIds[]                               = $order['id'];
            $user['orders'][$order['id']]['products'] = Pi::api('order', 'order')->listProduct($order['id']);
        }
        // Get invoice
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id'], true, $orderIds);
        // Set more link
        $block['more'] = Pi::url('order');
        // Set block array
        $block['resources'] = $user;

        $block['d'] = Pi::api('installment', 'order')->blockTable($user, $orderIds);
        return $block;
    }

    public function credit($options = [], $module = null)
    {
        // Set options
        $block = [];
        $block = array_merge($block, $options);

        return $block;
    }
}

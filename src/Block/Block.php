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
namespace Module\Order\Block;

use Pi;

class Block
{
    public static function detail($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);
        // Get user info
        $user = Pi::api('user', 'order')->getUserInformation();
        $user['orders'] = Pi::api('order', 'order')->getOrderFromUser($user['id']);
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id']);
        // Set more link 
        $block['more'] = Pi::url('order');
        // Set block array
        $block['resources'] = $user;
        return $block;
    }

    public static function installment($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);
        // Get user info
        $user = Pi::api('user', 'order')->getUserInformation();
        $user['orders'] = Pi::api('order', 'order')->getOrderFromUser($user['id'], true);
        $user['invoices'] = Pi::api('invoice', 'order')->getInvoiceFromUser($user['id'], true);
        // Set more link 
        $block['more'] = Pi::url('order');
        // Set block array
        $block['resources'] = $user;

        $block['d'] = Pi::api('installment', 'order')->blockTable($user);
        return $block;
    }
}
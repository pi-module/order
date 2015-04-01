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

/*
 * Pi::api('notification', 'order')->addOrder($user, $order);
 * Pi::api('notification', 'order')->payInvoice($user, $invoice);
 */

class Notification extends AbstractApi
{
    public function addOrder($user, $order)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
        	return false;
        }

        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Get admin main
        $adminmail = Pi::config('adminmail');
        $adminname = Pi::config('adminname');

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'controller'    => 'detail',
            'action'        => 'index',
            'id'            => $order['id'],
        )));

        // Set info
        $information = array(
        	'first_name' => $user['first_name'],
        	'last_name'  => $user['last_name'], 
        	'order_id'   => $order['id'],
        	'order_link' => $link,
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::api('mail', 'notification')->send($toAdmin, 'admin_add_order', $information, Pi::service('module')->current());

        // Send mail to user
        $toUser = array(
        	$user['email'] => sprintf('%s %s', $user['first_name'], $user['last_name']);
        );
        Pi::api('mail', 'notification')->send($toUser, 'user_add_order', $information, Pi::service('module')->current());

        // Send sms to admin
        Pi::api('sms', 'notification')->sendToAdmin($config['sms_order_admin']);

        // Send sms to user
        $content = sprintf($config['sms_order_user'], $user['first_name'], $user['last_name'])
        Pi::api('sms', 'notification')->send($content, $user['mobile']);
    }

    public function payInvoice($user, $invoice)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
        	return false;
        }
        
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Get admin main
        $adminmail = Pi::config('adminmail');
        $adminname = Pi::config('adminname');

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'controller'    => 'invoice',
            'action'        => 'index',
            'id'            => $invoice['id'],
        )));

        // Set info
        $information = array(
        	'first_name' => $user['first_name'],
        	'last_name'  => $user['last_name'], 
        	'invoice_id'   => $invoice['id'],
        	'invoice_link' => $link,
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::api('mail', 'notification')->send($toAdmin, 'admin_pay_invoice', $information, Pi::service('module')->current());

        // Send mail to user
        $toUser = array(
        	$user['email'] => sprintf('%s %s', $user['first_name'], $user['last_name']);
        );
        Pi::api('mail', 'notification')->send($toUser, 'user_pay_invoice', $information, Pi::service('module')->current());

        // Send sms to admin
        Pi::api('sms', 'notification')->sendToAdmin($config['sms_invoice_admin']);

        // Send sms to user
        $content = sprintf($config['sms_invoice_user'], $user['first_name'], $user['last_name'])
        Pi::api('sms', 'notification')->send($content, $user['mobile']);
    }
}
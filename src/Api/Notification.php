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
 * Pi::api('notification', 'order')->addOrder($order);
 * Pi::api('notification', 'order')->payInvoice($order, $invoice);
 * Pi::api('notification', 'order')->duedateInvoice($order, $invoice);
 * Pi::api('notification', 'order')->expiredInvoice($order, $invoice);
 * Pi::api('notification', 'order')->doCron();
 */

class Notification extends AbstractApi
{
    public function addOrder($order)
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
            'module'       => $this->getModule(),
            'controller'   => 'detail',
            'action'       => 'index',
            'id'           => $order['id'],
        )));

        // Set info
        $information = array(
            'first_name'   => $order['first_name'],
        	'last_name'    => $order['last_name'], 
        	'order_id'     => $order['id'],
        	'order_link'   => $link,
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::api('mail', 'notification')->send($toAdmin, 'admin_add_order', $information, Pi::service('module')->current());

        // Send mail to user
        $toUser = array(
        	$order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send($toUser, 'user_add_order', $information, Pi::service('module')->current());

        // Send sms to admin
        Pi::api('sms', 'notification')->sendToAdmin($config['sms_order_admin']);

        // Send sms to user
        $content = sprintf($config['sms_order_user'], $order['first_name'], $order['last_name']);
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function payInvoice($order, $invoice)
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
            'module'       => $this->getModule(),
            'controller'   => 'invoice',
            'action'       => 'index',
            'id'           => $invoice['id'],
        )));

        // Set info
        $information = array(
            'first_name'   => $order['first_name'],
            'last_name'    => $order['last_name'], 
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
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send($toUser, 'user_pay_invoice', $information, Pi::service('module')->current());

        // Send sms to admin
        Pi::api('sms', 'notification')->sendToAdmin($config['sms_invoice_admin']);

        // Send sms to user
        $content = sprintf($config['sms_invoice_user'], $order['first_name'], $order['last_name']);
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function duedateInvoice($order, $invoice)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }
        
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module'       => $this->getModule(),
            'controller'   => 'invoice',
            'action'       => 'index',
            'id'           => $invoice['id'],
        )));

        // Set info
        $information = array(
            'first_name'   => $order['first_name'],
            'last_name'    => $order['last_name'], 
            'invoice_id'   => $invoice['id'],
            'invoice_link' => $link,
            'day'          => $config['notification_cron_invoice'],
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send($toUser, 'user_duedate_invoice', $information, Pi::service('module')->current());

        // Send sms to user
        $content = sprintf($config['sms_invoice_duedate'], $order['first_name'], $order['last_name'], $config['notification_cron_invoice']);
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function expiredInvoice($order, $invoice)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }
        
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module'       => $this->getModule(),
            'controller'   => 'invoice',
            'action'       => 'index',
            'id'           => $invoice['id'],
        )));

        // Set info
        $information = array(
            'first_name'   => $order['first_name'],
            'last_name'    => $order['last_name'], 
            'invoice_id'   => $invoice['id'],
            'invoice_link' => $link,
            'day'          => $config['notification_cron_expired'],
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send($toUser, 'user_expired_invoice', $information, Pi::service('module')->current());

        // Send sms to user
        $content = sprintf($config['sms_invoice_expired'], $order['first_name'], $order['last_name'], $config['notification_cron_expired']);
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function doCron()
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }

        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // due date invoices
        $duedate1 = time() + (86400 * (intval($config['notification_cron_invoice']) + 1));
        $duedate2 = time() + (86400 * intval($config['notification_cron_invoice']));
        $where = array('status' => 2, 'time_duedate < ?' => $duedate1, 'time_duedate > ?' => $duedate2);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoice = $this->canonizeInvoice($row);
            $order = Pi::api('order', 'order')->getOrder($row->order);
            $this->duedateInvoice($order, $invoice);
        }


        // expired invoices
        $duedate1 = time() - (86400 * intval($config['notification_cron_invoice']));
        $duedate2 = time() - (86400 * (intval($config['notification_cron_invoice']) + 1));
        $where = array('status' => 2, 'time_duedate < ?' => $duedate1, 'time_duedate > ?' => $duedate2);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoice = $this->canonizeInvoice($row);
            $order = Pi::api('order', 'order')->getOrder($row->order);
            $this->expiredInvoice($order, $invoice);
        }
        
    }
}
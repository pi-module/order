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
 * Pi::api('notification', 'order')->processOrder($order, $type);
 * Pi::api('notification', 'order')->processOrderNote($order);
 * Pi::api('notification', 'order')->processOrderCanPay($order);
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
        $sitename = Pi::config('sitename');
        $adminmail = Pi::config('adminmail');
        $adminname = Pi::config('adminname');

        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        $productList = '';
        $productPrice = '';
        foreach ($order['products'] as $product) {
            $productPrice = $productPrice + $product['total_price'];
            $productList .= $product['details']['title'] . ' , ';
        }
        $productPrice = Pi::api('api', 'order')->viewPrice($productPrice);

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'detail',
            'action' => 'index',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'order_id' => $order['code'],
            'order_link' => $link,
            'product_list' => $productList,
            'product_price' => $productPrice,
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::api('mail', 'notification')->send(
            $toAdmin,
            'admin_add_order',
            $information,
            Pi::service('module')->current()
        );

        // Email to each module
        if (!empty($config['order_notification_email'])) {
            $lists = explode("|", $config['order_notification_email']);
            foreach ($lists as $items) {
                $list = explode(",", $items);
                if ($list[0] == $order['module_name']) {
                    // Send mail to admin
                    $toAdmin = array(
                        $list[1] => $adminname,
                    );
                    Pi::api('mail', 'notification')->send(
                        $toAdmin,
                        'admin_add_order',
                        $information,
                        Pi::service('module')->current()
                    );
                }
            }
        }

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send(
            $toUser,
            'user_add_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to admin
        $content = sprintf(
            $config['sms_order_admin'],
            $sitename,
            $order['first_name'],
            $order['last_name'],
            $productList,
            $productPrice
        );
        Pi::api('sms', 'notification')->sendToAdmin($content);

        // Send sms to user
        $content = sprintf(
            $config['sms_order_user'],
            $order['first_name'],
            $order['last_name'],
            $productList,
            $productPrice,
            $sitename
        );
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function processOrder($order, $type)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }

        // Get sitename
        $sitename = Pi::config('sitename');

        // Set status
        $status = '';
        switch ($type) {
            case 'order':
                switch ($order['status_order']) {
                    // Orders validated
                    case 2:
                        $status = __('Confirmed');
                        break;

                    // Orders pending
                    case 3:
                        $status = __('has pending to confirmed');
                        break;

                    // Orders finished
                    case 7:
                        $status = __('Finished');
                        break;

                }
                break;

            case 'payment':
                switch ($order['status_order']) {
                    // Paid
                    case 2:
                        $status = __('Paid');
                        break;
                }
                break;

            case 'delivery':
                switch ($order['status_order']) {
                    // Packed
                    case 2:
                        $status = __('Packed');
                        break;

                    // Posted
                    case 3:
                        $status = __('Posted');
                        break;

                    // Delivered
                    case 4:
                        $status = __('Delivered');
                        break;

                    // Back eaten
                    case 5:
                        $status = __('Back eaten');
                        break;
                }
                break;
        }

        // Check status
        if (empty($status)) {
            return false;
        }

        // Set mail text
        $text = sprintf(
            __('Your order by %s ID %s on %s website'),
            $order['code'],
            $status,
            $sitename
        );

        // Set sms content
        $content = sprintf(
            __('Dear %s %s, Your order by %s ID %s'),
            $order['first_name'],
            $order['last_name'],
            $order['code'],
            $status
        );

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'detail',
            'action' => 'index',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'order_link' => $link,
            'text' => $text,
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send(
            $toUser,
            'user_process_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function processOrderNote($order)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }

        // Get sitename
        $sitename = Pi::config('sitename');

        // Set mail text
        $text = sprintf(
            __('Admin note updated for order %s on %s website'),
            $order['code'],
            $sitename
        );

        // Set sms content
        $content = sprintf(
            __('Dear %s %s, Admin note updated for order %s'),
            $order['first_name'],
            $order['last_name'],
            $order['code']
        );

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'detail',
            'action' => 'index',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'order_link' => $link,
            'text' => $text,
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send(
            $toUser,
            'user_process_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        Pi::api('sms', 'notification')->send($content, $order['mobile']);
    }

    public function processOrderCanPay($order)
    {
        // Check notification module
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }

        if ($order['can_pay'] == 1) {
            // Get sitename
            $sitename = Pi::config('sitename');

            // Set mail text
            $text = sprintf(
                __('We active payment for order %s on %s website, and you can pay it now'),
                $order['code'],
                $sitename
            );

            // Set sms content
            $content = sprintf(
                __('Dear %s %s, We active payment for order %s on %s website, and you can pay it now'),
                $order['first_name'],
                $order['last_name'],
                $order['code'],
                $sitename
            );

            // Set link
            $link = Pi::url(Pi::service('url')->assemble('order', array(
                'module' => $this->getModule(),
                'controller' => 'detail',
                'action' => 'index',
                'id' => $order['id'],
            )));

            // Set mail information
            $information = array(
                'first_name' => $order['first_name'],
                'last_name' => $order['last_name'],
                'order_link' => $link,
                'text' => $text,
            );

            // Send mail to user
            $toUser = array(
                $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
            );
            Pi::api('mail', 'notification')->send(
                $toUser,
                'user_process_order',
                $information,
                Pi::service('module')->current(),
                $order['uid']
            );

            // Send sms to user
            Pi::api('sms', 'notification')->send($content, $order['mobile']);
        }
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
        $sitename = Pi::config('sitename');
        $adminmail = Pi::config('adminmail');
        $adminname = Pi::config('adminname');

        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $order['module_name']);
        $productList = '';
        foreach ($order['products'] as $product) {
            $productList .= $product['details']['title'] . ' , ';
        }

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'order',
            'action' => 'index',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'order_id' => $order['code'],
            'invoice_id' => $invoice['code'],
            'order_link' => $link,
            'invoice_price' => Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            'product_list' => $productList,
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::api('mail', 'notification')->send(
            $toAdmin,
            'admin_pay_invoice',
            $information,
            Pi::service('module')->current()
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send(
            $toUser,
            'user_pay_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to admin
        $content = sprintf(
            $config['sms_invoice_admin'],
            $sitename,
            $order['first_name'],
            $order['last_name'],
            $invoice['code'],
            Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            $order['code'],
            $productList
        );
        Pi::api('sms', 'notification')->sendToAdmin($content);

        // Send sms to user
        $content = sprintf(
            $config['sms_invoice_user'],
            $order['first_name'],
            $order['last_name'],
            $invoice['code'],
            Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            $order['code'],
            $productList,
            $sitename
        );
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
            'module' => $this->getModule(),
            'controller' => 'order',
            'action' => 'index',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'invoice_id' => $invoice['id'],
            'order_link' => $link,
            'day' => $config['notification_cron_invoice'],
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send(
            $toUser,
            'user_duedate_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

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
            'module' => $this->getModule(),
            'controller' => 'order',
            'action' => 'index',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'invoice_id' => $invoice['id'],
            'order_link' => $link,
            'day' => $config['notification_cron_expired'],
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::api('mail', 'notification')->send(
            $toUser,
            'user_expired_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

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

        // Set log
        $file = Pi::path('upload/order/log.txt');
        if (!Pi::service('file')->exists($file)) {
            Pi::service('file')->mkdir(Pi::path('upload/order'));
            $fp = fopen($file, "a+");
            fclose($fp);
        }
        $buffer = json_encode(array('cron start on server time', date("Y-m-d H:i:s")));
        if (file_exists($file)) {
            $buffer = file_get_contents($file) . "\n" . $buffer;
        }
        file_put_contents($file, $buffer);

        // due date invoices
        // $duedate1 = time() + (86400 * intval($config['notification_cron_invoice']));
        // $duedate2 = time() + (86400 * (intval($config['notification_cron_invoice']) + 1));
        $duedate1 = time();
        $duedate2 = time() + (86400 * intval($config['notification_cron_invoice']));
        $where = array('status' => 2, 'time_duedate > ?' => $duedate1, 'time_duedate < ?' => $duedate2);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoice = Pi::api('invoice', 'order')->canonizeInvoice($row);
            $order = Pi::api('order', 'order')->getOrder($row->order);
            $this->duedateInvoice($order, $invoice);
            // Set log
            $buffer = file_get_contents($file) . "\n" . json_encode(array('due date invoice', $order, $invoice));
            file_put_contents($file, $buffer);
        }

        // expired invoices
        //$duedate1 = time() - (86400 * intval($config['notification_cron_invoice']));
        //$duedate2 = time() - (86400 * (intval($config['notification_cron_invoice']) + 1));
        //$where = array('status' => 2, 'time_duedate < ?' => $duedate1, 'time_duedate > ?' => $duedate2);
        $time = time() - 86400;
        $where = array('status' => 2, 'time_duedate < ?' => $time);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoice = Pi::api('invoice', 'order')->canonizeInvoice($row);
            $order = Pi::api('order', 'order')->getOrder($row->order);
            $this->expiredInvoice($order, $invoice);
            // Set log
            $buffer = file_get_contents($file) . "\n" . json_encode(array('expired invoice', $order, $invoice));
            file_put_contents($file, $buffer);
        }
    }
}
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

        // type product
        $typeProduct = "undefined";
        if ($order['module_name'] == 'guide') {
            $typeProduct = __('package');
        } else if ($order['module_name'] == 'shop') {
            $typeProduct = __('product');
        } else {
            $typeProduct = __($order['module_name']);
        }   
        
        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'order_id' => (!empty($order['code'])) ? $order['code'] : $order['id'],
            'order_link' => $link,
            'product_list' => $productList,
            'product_price' => $productPrice,
            'type_product' => $typeProduct,
            
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::service('notification')->send(
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
                    Pi::service('notification')->send(
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
        Pi::service('notification')->send(
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
        Pi::service('notification')->smsToAdmin($content);

        // Send sms to user
        $content = sprintf(
            $config['sms_order_user'],
            $order['first_name'],
            $order['last_name'],
            $productList,
            $productPrice,
            $sitename
        );
        Pi::service('notification')->smsToUser($content, $order['mobile']);
    }

    public function processOrder($order, $type)
    {
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
        Pi::service('notification')->send(
            $toUser,
            'user_process_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        Pi::service('notification')->smsToUser($content, $order['mobile']);
    }

    public function processOrderNote($order)
    {
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
        Pi::service('notification')->send(
            $toUser,
            'user_process_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        Pi::service('notification')->smsToUser($content, $order['mobile']);
    }

    public function processOrderCanPay($order)
    {
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
            Pi::service('notification')->send(
                $toUser,
                'user_process_order',
                $information,
                Pi::service('module')->current(),
                $order['uid']
            );

            // Send sms to user
            Pi::service('notification')->smsToUser($content, $order['mobile']);

        }
    }

    public function payInvoice($order, $invoice)
    {
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

        // type product
        $typeProduct = "undefined";
        if ($order['module_name'] == 'guide') {
            $typeProduct = __('package');
        } else if ($order['module_name'] == 'shop') {
            $typeProduct = __('product');
        } else {
            $typeProduct = __($order['module_name']);
        }
        
        // Set mail information
        $information = array(
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'order_id' => $order['code'],
            'invoice_id' => $invoice['code'],
            'order_link' => $link,
            'invoice_price' => Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            'product_list' => $productList,
            'type_product' => $typeProduct,
        );

        // Send mail to admin
        $toAdmin = array(
            $adminmail => $adminname,
        );
        Pi::service('notification')->send(
            $toAdmin,
            'admin_pay_invoice',
            $information,
            Pi::service('module')->current()
        );

        // Send mail to user
        $toUser = array(
            $order['email'] => sprintf('%s %s', $order['first_name'], $order['last_name']),
        );
        Pi::service('notification')->send(
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
        Pi::service('notification')->smsToAdmin($content);

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
        Pi::service('notification')->smsToUser($content, $order['mobile']);
    }

    public function duedateInvoice($order, $invoice)
    {
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
        Pi::service('notification')->send(
            $toUser,
            'user_duedate_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        $content = sprintf($config['sms_invoice_duedate'], $order['first_name'], $order['last_name'], $config['notification_cron_invoice']);
        Pi::service('notification')->smsToUser($content, $order['mobile']);
    }

    public function expiredInvoice($order, $invoice)
    {
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
        Pi::service('notification')->send(
            $toUser,
            'user_expired_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        $content = sprintf($config['sms_invoice_expired'], $order['first_name'], $order['last_name'], $config['notification_cron_expired']);
        Pi::service('notification')->smsToUser($content, $order['mobile']);
    }
}
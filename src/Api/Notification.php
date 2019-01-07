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
    public function addOrder($order, $addressInvoicing, $oneMail = false)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Get admin main
        $sitename = Pi::config('sitename');
        $adminmail = Pi::config('adminmail');
        $adminname = Pi::config('adminname');

        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id']);
        $productList = '';
        $totalPrice = 0;
        $typeProduct = array();
        foreach ($order['products'] as $product) {
            $productPrice = $product['product_price'] + $product['vat_price'] + $product['setup_price'] + $product['packing_price'] + $product['shipping_price'] - $product['discount_price']; 
            $totalPrice += $productPrice;
            $productList .= $product['details']['title'] . ' , ';
            
            if ($product['module'] == 'guide') {
                $typeProduct[] = __('package');
            } else if ($product['module'] == 'shop') {
                $typeProduct[] = __('product');
            } else {
                $typeProduct[] = __($product['module']);
            }
        }
        $typeProduct = array_unique($typeProduct);
        $productPrice = Pi::api('api', 'order')->viewPrice($totalPrice);

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'action' => 'print',
            'id' => $order['id'],
        )));

        
        
        $userNote = '';
        if ($order['user_note']) {
            $userNote = Pi::service('mail')->template(
                array(
                    'file'      => 'admin_user_note',
                    'module'    => 'order',
                ),
                array(
                    'user_note' => $order['user_note'],                    
                )
            );
        }
        // Set mail information
        $information = array(
            'first_name' => $addressInvoicing['first_name'],
            'last_name' => $addressInvoicing['last_name'],
            'order_id' => (!empty($order['code'])) ? $order['code'] : $order['id'],
            'order_link' => $link,
            'product_list' => $productList,
            'product_price' => $productPrice,
            'type_product' => join(', ', $typeProduct),
            'sellerinfo' => $config['order_sellerinfo'],
            'user_note' => $userNote['body']
            
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
                if (in_array($list[0], $typeProduct)) {
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

        if (!$oneMail) {   
            // Send mail to user
            $toUser = array(
                $addressInvoicing['email'] => sprintf('%s %s', $addressInvoicing['first_name'], $addressInvoicing['last_name']),
            );
            Pi::service('notification')->send(
                $toUser,
                'user_add_order',
                $information,
                Pi::service('module')->current(),
                $order['uid']
            );
        }

        // Send sms to admin
        $content = sprintf(
            $config['sms_order_admin'],
            $sitename,
            $addressInvoicing['first_name'],
            $addressInvoicing['last_name'],
            $productList,
            $productPrice
        );
        Pi::service('notification')->smsToAdmin($content);

        if (!$oneMail) {
            // Send sms to user
            $content = sprintf(
                $config['sms_order_user'],
                $addressInvoicing['first_name'],
                $addressInvoicing['last_name'],
                $productList,
                $productPrice,
                $sitename
            );
            Pi::service('notification')->smsToUser($content, $addressInvoicing['mobile']);
        }
    }

    public function processOrder($order, $type)
    {
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // Get sitename
        $sitename = Pi::config('sitename');

        // Set status
        $status = '';
        switch ($type) {
            case 'order':
                switch ($order['status_order']) {
                    // Orders validated
                    case \Module\Order\Model\Order::STATUS_ORDER_VALIDATED:
                        $status = __('Confirmed');
                        break;

                    // Orders pending
                    case \Module\Order\Model\Order::STATUS_ORDER_CANCELLED:
                        $status = __('Candeled');
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
            $address['first_name'],
            $address['last_name'],
            $order['code'],
            $status
        );

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'action' => 'print',
            'id' => $order['id'],
        )));

        // Set mail information
        $config = Pi::service('registry')->config->read($this->getModule());

        $information = array(
            'first_name' => $address['first_name'],
            'last_name' => $address['last_name'],
            'order_link' => $link,
            'text' => $text,
            'sellerinfo' => $config['order_sellerinfo']

        );

        // Send mail to user
        $toUser = array(
            $address['email'] => sprintf('%s %s', $address['first_name'], $address['last_name']),
        );
        Pi::service('notification')->send(
            $toUser,
            'user_process_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        Pi::service('notification')->smsToUser($content, $address['mobile']);
    }

    public function processOrderNote($order)
    {
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // Get sitename
        $sitename = Pi::config('sitename');
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set mail text
        $text = sprintf(
            __('Admin note updated for order %s on %s website'),
            $order['code'],
            $sitename
        );

        // Set sms content
        $content = sprintf(
            __('Dear %s %s, Admin note updated for order %s'),
            $address['first_name'],
            $address['last_name'],
            $order['code']
        );

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'action' => 'print',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $address['first_name'],
            'last_name' => $address['last_name'],
            'order_link' => $link,
            'text' => $text,
            'sellerinfo' => $config['order_sellerinfo']
            
        );

        // Send mail to user
        $toUser = array(
            $address['email'] => sprintf('%s %s', $address['first_name'], $address['last_name']),
        );
        Pi::service('notification')->send(
            $toUser,
            'user_process_order',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        Pi::service('notification')->smsToUser($content, $address['mobile']);
    }

    public function processOrderCanPay($order)
    {
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        $config = Pi::service('registry')->config->read($this->getModule());
        
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
                $address['first_name'],
                $address['last_name'],
                $order['code'],
                $sitename
            );

            // Set link
            $link = Pi::url(Pi::service('url')->assemble('order', array(
                'module' => $this->getModule(),
                'controller' => 'index',
                'action' => 'print',
                'id' => $order['id'],
            )));

            // Set mail information
            $information = array(
                'first_name' => $address['first_name'],
                'last_name' => $address['last_name'],
                'order_link' => $link,
                'text' => $text,
                'sellerinfo' => $config['order_sellerinfo']
                
            );

            // Send mail to user
            $toUser = array(
                $address['email'] => sprintf('%s %s', $address['first_name'], $address['last_name']),
            );
            Pi::service('notification')->send(
                $toUser,
                'user_process_order',
                $information,
                Pi::service('module')->current(),
                $order['uid']
            );

            // Send sms to user
            Pi::service('notification')->smsToUser($content, $address['mobile']);

        }
    }

    public function payInvoice($order, $invoice)
    {
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Get admin main
        $sitename = Pi::config('sitename');
        $adminmail = Pi::config('adminmail');
        $adminname = Pi::config('adminname');

        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id']);
        $productList = array();
        $typeProduct = array();
        foreach ($order['products'] as $product) {
            $productList[] = $product['details']['title'];
            if ($product['module'] == 'guide') {
                $typeProduct[] = __('package');
            } else if ($product['module'] == 'shop') {
                $typeProduct[] = __('product');
            } else {
                $typeProduct[] = __($product['module']);
            }
        }
        $productList = join(', ', $productList);
        
        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'action' => 'print',
            'id' => $order['id'],
        )));

        
        // Set mail information
        $information = array(
            'first_name' => $address['first_name'],
            'last_name' => $address['last_name'],
            'order_id' => $order['code'],
            'invoice_id' => $invoice['code'],
            'order_link' => $link,
            'invoice_price' => Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            'product_list' => $productList,
            'type_product' => join(', ', $typeProduct),
            'sellerinfo' => $config['order_sellerinfo']
            
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
            $address['email'] => sprintf('%s %s', $address['first_name'], $address['last_name']),
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
            $address['first_name'],
            $address['last_name'],
            $invoice['code'],
            Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            $order['code'],
            $productList
        );
        Pi::service('notification')->smsToAdmin($content);

        // Send sms to user
        $content = sprintf(
            $config['sms_invoice_user'],
            $address['first_name'],
            $address['last_name'],
            $invoice['code'],
            Pi::api('api', 'order')->viewPrice($invoice['total_price']),
            $order['code'],
            $productList,
            $sitename
        );
        Pi::service('notification')->smsToUser($content, $address['mobile']);
    }

    public function duedateInvoice($order, $invoice)
    {
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'action' => 'print',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $address['first_name'],
            'last_name' => $address['last_name'],
            'invoice_id' => $invoice['id'],
            'order_link' => $link,
            'day' => $config['notification_cron_invoice'],
            'sellerinfo' => $config['order_sellerinfo']
            
        );

        // Send mail to user
        $toUser = array(
            $address['email'] => sprintf('%s %s', $address['first_name'], $address['last_name']),
        );
        Pi::service('notification')->send(
            $toUser,
            'user_duedate_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        $content = sprintf($config['sms_invoice_duedate'], $address['first_name'], $address['last_name'], $config['notification_cron_invoice']);
        Pi::service('notification')->smsToUser($content, $address['mobile']);
    }

    public function expiredInvoice($order, $invoice)
    {
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Set link
        $link = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'index',
            'action' => 'print',
            'id' => $order['id'],
        )));

        // Set mail information
        $information = array(
            'first_name' => $address['first_name'],
            'last_name' => $address['last_name'],
            'invoice_id' => $invoice['id'],
            'order_link' => $link,
            'day' => $config['notification_cron_expired'],
            'sellerinfo' => $config['order_sellerinfo']
            
        );

        // Send mail to user
        $toUser = array(
            $address['email'] => sprintf('%s %s', $address['first_name'], $address['last_name']),
        );
        Pi::service('notification')->send(
            $toUser,
            'user_expired_invoice',
            $information,
            Pi::service('module')->current(),
            $order['uid']
        );

        // Send sms to user
        $content = sprintf($config['sms_invoice_expired'], $address['first_name'], $address['last_name'], $config['notification_cron_expired']);
        Pi::service('notification')->smsToUser($content, $address['mobile']);
    }
}

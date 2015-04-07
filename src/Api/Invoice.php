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
 * Pi::api('invoice', 'order')->createInvoice($id);
 * Pi::api('invoice', 'order')->getInvoice($id);
 * Pi::api('invoice', 'order')->getInvoiceFromOrder($order);
 * Pi::api('invoice', 'order')->getInvoiceFromUser($uid, $compressed);
 * Pi::api('invoice', 'order')->getInvoiceForPayment($id);
 * Pi::api('invoice', 'order')->updateInvoice($randomId);
 * Pi::api('invoice', 'order')->canonizeInvoice($invoice);
 * Pi::api('invoice', 'order')->setBackUrl($id, $url);
 */

class Invoice extends AbstractApi
{
    /**
     * Create Invoice
     *
     * @return array
     */
    public function createInvoice($id)
    {
        // Get order
        $order = Pi::api('order', 'order')->getOrder($id);
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get user
        $uid = Pi::user()->getId();
        // Check user
        if ($config['order_anonymous'] == 0 && $uid == 0) {
            $result['status'] = 0;
            $result['pay_url'] = '';
            $result['message'] = __('Please login for create invoice');
        } else {
            // Check type_payment
            switch ($order['type_payment']) {
                case 'free':
                    // Set invoice
                    $row = Pi::model('invoice', $this->getModule())->createRow();
                    $row->random_id = time() + rand(100, 999);
                    $row->uid = $uid;
                    $row->ip = Pi::user()->getIp();
                    $row->status = 1;
                    $row->time_create = time();
                    $row->time_duedate = time();
                    $row->order = $order['id'];
                    $row->product_price = $order['product_price'];
                    $row->discount_price = $order['discount_price'];
                    $row->shipping_price = $order['shipping_price'];
                    $row->packing_price = $order['packing_price'];
                    $row->vat_price = $order['vat_price'];
                    $row->total_price = $order['total_price'];
                    $row->paid_price = 0;
                    $row->credit_price = 0;
                    $row->gateway = $order['gateway'];
                    $row->save();
                    break;
            
                case 'onetime':
                case 'recurring':
                    // Set invoice
                    $row = Pi::model('invoice', $this->getModule())->createRow();
                    $row->random_id = time() + rand(100, 999);
                    $row->uid = $uid;
                    $row->ip = Pi::user()->getIp();
                    $row->status = 2;
                    $row->time_create = time();
                    $row->time_duedate = time();
                    $row->order = $order['id'];
                    $row->product_price = $order['product_price'];
                    $row->discount_price = $order['discount_price'];
                    $row->shipping_price = $order['shipping_price'];
                    $row->packing_price = $order['packing_price'];
                    $row->vat_price = $order['vat_price'];
                    $row->total_price = $order['total_price'];
                    $row->paid_price = 0;
                    $row->credit_price = 0;
                    $row->gateway = $order['gateway'];
                    $row->save();
                    // return array
                    $result['status'] = $row->status;
                    $result['message'] = __('Your invoice create successfully');
                    $result['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module'        => $this->getModule(),
                        'controller'    => 'detail',
                        'action'        => 'index',
                        'id'            => $row->order,
                    )));
                    $result['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module'        => $this->getModule(),
                        'controller'    => 'invoice',
                        'action'        => 'index',
                        'id'            => $row->id,
                    )));
                    $result['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module'        => $this->getModule(),
                        'controller'    => 'payment',
                        'action'        => 'index',
                        'id'            => $row->id,
                    )));
                    // Set invoice information on session
                    if ($config['order_anonymous'] == 1 && $uid == 0) {
                        $_SESSION['payment']['process'] = 1;
                        $_SESSION['payment']['process_start'] = time();
                        $_SESSION['payment']['invoice_id'] = $row->id;
                        $_SESSION['payment']['gateway'] = $row->gateway;
                    }
                    break;

                case 'installment':
                    // Get user 
                    $user = Pi::api('user', 'order')->getUserInformation();
                    // Set invoices price
                    $invoices = Pi::api('installment', 'order')->setPriceForInvoice($order['total_price'], $order['plan'], $user);
                    $total = $invoices['total'];
                    unset($invoices['total']);
                    // Check allowed
                    if ($total['allowed']) {
                        // Set invoices
                        foreach ($invoices as $key => $invoice) {
                            // Set invoice
                            $row = Pi::model('invoice', $this->getModule())->createRow();
                            $row->random_id = time() + rand(100, 999);
                            $row->uid = $uid;
                            $row->ip = Pi::user()->getIp();
                            $row->status = 2;
                            $row->time_create = time();
                            $row->time_duedate = $invoice['duedate'];
                            $row->order = $order['id'];
                            $row->product_price = $invoice['price'];
                            $row->discount_price = 0;
                            $row->shipping_price = 0;
                            $row->packing_price = 0;
                            $row->vat_price = 0;
                            $row->total_price = $invoice['price'];
                            $row->paid_price = 0;
                            $row->credit_price = $invoice['credit'];
                            $row->gateway = $order['gateway'];
                            $row->save();
                            // Set return
                            if ($key == 0) {
                                $information = array(
                                    'status'   => $row->status,
                                    'invoice'  => $row->id,
                                );
                            }
                        }
                        // Update user credit
                        if ($config['installment_credit']) {
                            $credit = $user['credit'] - $total['installment'];
                            Pi::model('profile', 'user')->update(array('credit' => $credit), array('uid' => $uid));
                        }
                        // return array
                        $result['status'] = $information['status'];
                        $result['message'] = __('Your invoice create successfully');
                        $result['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module'        => $this->getModule(),
                            'controller'    => 'detail',
                            'action'        => 'index',
                            'id'            => $order['id'],
                        )));
                        $result['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module'        => $this->getModule(),
                            'controller'    => 'invoice',
                            'action'        => 'index',
                            'id'            => $information['invoice'],
                        )));
                        $result['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module'        => $this->getModule(),
                            'controller'    => 'payment',
                            'action'        => 'index',
                            'id'            => $information['invoice'],
                        )));
                    } else {
                        $result['status'] = 0;
                        $result['message'] = __('Not allowed to create invoice by this user credit');
                        $result['order_url'] = '';
                        $result['invoice_url'] = '';
                        $result['pay_url'] = '';
                    }
                    break;  
            }
        }
        // return
        return $result;
    }

    public function getInvoice($parameter, $type = 'id')
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($parameter, $type);
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function getInvoiceFromOrder($order)
    {
        $invoices = array();
        $where = array('order' => $order);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoices[$row->id] = $this->canonizeInvoice($row);
            $invoices[$row->id]['log'] = Pi::api('log', 'order')->getTrueLog($row->id);
        }
        return $invoices;
    }

    public function getInvoiceFromUser($uid, $compressed = false)
    {
        $invoices = array();
        // Check compressed
        if ($compressed) {
            $where = array('uid' => $uid, 'status' => 2, 'time_duedate < ?' => strtotime('+1 month'));
        } else {
            $where = array('uid' => $uid);
        }
        // Select
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoices[$row->id] = $this->canonizeInvoice($row);
        }
        return $invoices;
    }

    public function getInvoiceForPayment($id)
    {
        // Set random id
        $rand = Rand::getInteger(10, 99);
        // Get invoice
        $invoice = Pi::model('invoice', $this->getModule())->find($id);
        // Update invoice
        $invoice->random_id = sprintf('%s%s', $invoice->id, $rand);
        $invoice->save();
        // Canonize invoice
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function updateInvoice($randomId)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $invoice = Pi::model('invoice', $this->getModule())->find($randomId, 'random_id');
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Update invoice
        $invoice->status = 1;
        $invoice->time_payment = time();
        $invoice->save();
        // Update user credit
        if ($config['installment_credit'] && $order['type_payment'] == 'installment') {
            // Get user
            $user = Pi::api('user', 'order')->getUserInformation();
            $uid = Pi::user()->getId();
            // Update
            $credit = $user['credit'] + $invoice->credit_price;
            Pi::model('profile', 'user')->update(array('credit' => $credit), array('uid' => $uid));
        }
        // Canonize invoice
        $invoice = $this->canonizeInvoice($invoice);
        // Send notification
        Pi::api('notification', 'order')->payInvoice($order, $invoice);
        return $invoice;
    }

    public function canonizeInvoice($invoice)
    {
        // Check
        if (empty($invoice)) {
            return '';
        }
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // boject to array
        $invoice = $invoice->toArray();
        // Set time
        $invoice['time_create_view'] = _date($invoice['time_create'], array('pattern' => 'yyyy-MM-dd'));
        $invoice['time_duedate_view'] = _date($invoice['time_duedate'], array('pattern' => 'yyyy-MM-dd'));
        $invoice['time_payment_view'] = $invoice['time_payment'] ? _date($invoice['time_payment'], array('pattern' => 'yyyy-MM-dd')) : __('Not pay');
        $invoice['time_cancel_view'] = $invoice['time_cancel'] ? _date($invoice['time_cancel'], array('pattern' => 'yyyy-MM-dd')) : __('Not canceled');
        // Set price
        $invoice['product_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['product_price']);
        $invoice['shipping_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['shipping_price']);
        $invoice['packing_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['packing_price']);
        $invoice['vat_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['vat_price']);
        $invoice['total_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['total_price']);
        $invoice['paid_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['paid_price']);
        // Set url
        $invoice['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'controller'    => 'detail',
            'action'        => 'index',
            'id'            => $invoice['order'],
        )));
        $invoice['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'controller'    => 'invoice',
            'action'        => 'index',
            'id'            => $invoice['id'],
        )));
        $invoice['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'controller'    => 'payment',
            'action'        => 'index',
            'id'            => $invoice['id'],
        )));
        // return order
        return $invoice; 
    }

    public function setBackUrl($id, $url)
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($id);
        $invoice->back_url = $url;
        $invoice->save();
    }
}	
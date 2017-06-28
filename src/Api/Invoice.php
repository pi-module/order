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
 * Pi::api('invoice', 'order')->generatCode($id);
 * Pi::api('invoice', 'order')->getInvoice($id);
 * Pi::api('invoice', 'order')->getInvoiceFromOrder($order, $getLog);
 * Pi::api('invoice', 'order')->getInvoiceFromUser($uid, $compressed, $orderIds);
 * Pi::api('invoice', 'order')->getInvoiceForPayment($id);
 * Pi::api('invoice', 'order')->cancelInvoiceFromOrder($order);
 * Pi::api('invoice', 'order')->updateInvoice($randomId);
 * Pi::api('invoice', 'order')->canonizeInvoice($invoice);
 * Pi::api('invoice', 'order')->setBackUrl($id, $url);
 * Pi::api('invoice', 'order')->getInvoiceScore($uid);
 */

class Invoice extends AbstractApi
{
    /**
     * Create Invoice
     *
     * @return array
     */
    public function createInvoice($id, $uid = null)
    {
        // Get order
        $order = Pi::api('order', 'order')->getOrder($id);
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get user
        $uid = (is_null($uid)) ? Pi::user()->getId() : $uid;
        // Check user
        if ($config['order_anonymous'] == 0 && $uid == 0) {
            $result['status'] = 0;
            $result['pay_url'] = '';
            $result['pay_credit_url'] = '';
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
                    $row->can_pay = $order['can_pay'];
                    $row->time_create = $order['time_create'];
                    $row->time_duedate = time();
                    $row->order = $order['id'];
                    $row->product_price = $order['product_price'];
                    $row->discount_price = $order['discount_price'];
                    $row->shipping_price = $order['shipping_price'];
                    $row->packing_price = $order['packing_price'];
                    $row->setup_price = $order['setup_price'];
                    $row->vat_price = $order['vat_price'];
                    $row->total_price = $order['total_price'];
                    $row->paid_price = 0;
                    $row->credit_price = 0;
                    $row->gateway = $order['gateway'];
                    $row->save();
                    // Set order ID
                    $code = $this->generatCode($row->id);
                    Pi::model('invoice', $this->getModule())->update(
                        array('code' => $code),
                        array('id' => $row->id)
                    );
                    break;

                case 'onetime':
                case 'recurring':
                    // Set invoice
                    $row = Pi::model('invoice', $this->getModule())->createRow();
                    $row->random_id = time() + rand(100, 999);
                    $row->uid = $uid;
                    $row->ip = Pi::user()->getIp();
                    $row->status = 2;
                    $row->can_pay = $order['can_pay'];
                    $row->time_create = $order['time_create'];
                    $row->time_duedate = time();
                    $row->order = $order['id'];
                    $row->product_price = $order['product_price'];
                    $row->discount_price = $order['discount_price'];
                    $row->shipping_price = $order['shipping_price'];
                    $row->packing_price = $order['packing_price'];
                    $row->setup_price = $order['setup_price'];
                    $row->vat_price = $order['vat_price'];
                    $row->total_price = $order['total_price'];
                    $row->paid_price = 0;
                    $row->credit_price = 0;
                    $row->gateway = $order['gateway'];
                    $row->save();
                    // Set order ID
                    $code = $this->generatCode($row->id);
                    Pi::model('invoice', $this->getModule())->update(
                        array('code' => $code),
                        array('id' => $row->id)
                    );
                    // return array
                    $result['status'] = $row->status;
                    $result['message'] = __('Your invoice create successfully');
                    $result['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module' => $this->getModule(),
                        'controller' => 'detail',
                        'action' => 'index',
                        'id' => $row->order,
                    )));
                    $result['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module' => $this->getModule(),
                        'controller' => 'invoice',
                        'action' => 'index',
                        'id' => $row->id,
                    )));
                    $result['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module' => $this->getModule(),
                        'controller' => 'payment',
                        'action' => 'index',
                        'id' => $row->id,
                    )));
                    $result['pay_credit_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module' => $this->getModule(),
                        'controller' => 'payment',
                        'action' => 'index',
                        'id' => $row->id,
                        'credit' => 1,
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
                    $invoices = Pi::api('installment', 'order')->setPriceForInvoice($order['product_price'], $order['plan'], $user);
                    $total = $invoices['total'];
                    unset($invoices['total']);
                    // Check allowed
                    if ($total['allowed']) {
                        // Set invoices
                        foreach ($invoices as $key => $invoice) {
                            // Set extra
                            $extra = array();
                            $extra['order']['type_payment'] = $order['type_payment'];
                            $extra['order']['type_commodity'] = $order['type_commodity'];
                            $extra['number'] = $key;
                            if ($key == 0) {
                                $extra['type'] = 'prepayment';
                            } else {
                                $extra['type'] = 'installment';
                            }
                            // Set invoice
                            $row = Pi::model('invoice', $this->getModule())->createRow();
                            $row->random_id = time() + rand(100, 999);
                            $row->uid = $uid;
                            $row->ip = Pi::user()->getIp();
                            $row->status = 2;
                            $row->can_pay = $order['can_pay'];
                            $row->time_create = $order['time_create'];
                            $row->time_duedate = $invoice['duedate'];
                            $row->order = $order['id'];
                            $row->product_price = $invoice['price'];
                            if ($key == 0) {
                                $row->discount_price = $order['discount_price'];
                                $row->shipping_price = $order['shipping_price'];
                                $row->setup_price = $order['setup_price'];
                                $row->packing_price = $order['packing_price'];
                                $row->vat_price = $order['vat_price'];
                            } else {
                                $row->discount_price = 0;
                                $row->shipping_price = 0;
                                $row->setup_price = 0;
                                $row->packing_price = 0;
                                $row->vat_price = 0;
                            }
                            $row->total_price = $invoice['price'];
                            $row->paid_price = 0;
                            $row->credit_price = $invoice['credit'];
                            $row->gateway = $order['gateway'];
                            $row->extra = json::encode($extra);
                            $row->save();
                            // Set order ID
                            $code = $this->generatCode($row->id);
                            Pi::model('invoice', $this->getModule())->update(
                                array('code' => $code),
                                array('id' => $row->id)
                            );
                            // Set return
                            if ($key == 0) {
                                $information = array(
                                    'status' => $row->status,
                                    'invoice' => $row->id,
                                );
                            }
                        }
                        // Update user credit
                        if ($config['installment_credit']) {
                            $message = __('Decrease credit for installment system');
                            Pi::api('credit', 'order')->addCredit($uid, $total['installment'], 'decrease', 'automatic', $message, $message);
                        }
                        // Update order
                        $totalPrice = ($total['price'] + $order['shipping_price'] + $order['packing_price'] + $order['setup_price'] + $order['vat_price']) - $order['discount_price'];
                        Pi::model('order', 'order')->update(
                            array('product_price' => $total['price'], 'total_price' => $totalPrice),
                            array('id' => $order['id'])
                        );
                        // return array
                        $result['status'] = $information['status'];
                        $result['message'] = __('Your invoice create successfully');
                        $result['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module' => $this->getModule(),
                            'controller' => 'detail',
                            'action' => 'index',
                            'id' => $order['id'],
                        )));
                        $result['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module' => $this->getModule(),
                            'controller' => 'invoice',
                            'action' => 'index',
                            'id' => $information['invoice'],
                        )));
                        $result['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module' => $this->getModule(),
                            'controller' => 'payment',
                            'action' => 'index',
                            'id' => $information['invoice'],
                        )));
                        $result['pay_credit_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                            'module' => $this->getModule(),
                            'controller' => 'payment',
                            'action' => 'index',
                            'id' => $information['invoice'],
                            'credit' => 1,
                        )));
                    } else {
                        $result['status'] = 0;
                        $result['message'] = __('Not allowed to create invoice by this user credit');
                        $result['order_url'] = '';
                        $result['invoice_url'] = '';
                        $result['pay_url'] = '';
                        $result['pay_credit_url'] = '';
                    }
                    break;
            }
        }
        // return
        return $result;
    }

    public function generatCode($id = '')
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        $prefix = $config['invoice_code_prefix'];
        // Check ID
        if (empty($id)) {
            // Generate random code
            $id = Rand::getInteger(10000000, 99999999);
        }
        // Generate order code
        $code = sprintf('%s-%s', $prefix, $id);
        return $code;
    }

    public function getInvoice($parameter, $type = 'id')
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($parameter, $type);
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function getInvoiceFromOrder($order, $getLog = true)
    {
        $invoices = array();
        $where = array('order' => $order['id']);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoices[$row->id] = $this->canonizeInvoice($row);
            // Get log
            if ($getLog) {
                $invoices[$row->id]['log'] = Pi::api('log', 'order')->getTrueLog($row->id);
            }
            // Check allow payment
            /* if ($order['type_payment'] == 'installment') {
                if ($invoices[$row->id]['extra']['type'] == 'installment') {
                    $time = time() + (60 * 60 * 24 * 28);
                    if ($invoices[$row->id]['time_duedate'] < $time) {
                        $invoices[$row->id]['allowPayment'] = 1;
                    } else {
                        $invoices[$row->id]['allowPayment'] = 0;
                    }
                } else {
                    $invoices[$row->id]['allowPayment'] = 1;
                }
            } else {
                $invoices[$row->id]['allowPayment'] = 1;
            } */
            $invoices[$row->id]['allowPayment'] = 1;
        }
        return $invoices;
    }

    public function getInvoiceFromUser($uid, $compressed = false, $orderIds = array())
    {
        $invoices = array();
        // Check compressed
        if ($compressed) {
            $where = array('uid' => $uid, 'status' => 2, 'time_duedate < ?' => strtotime('+1 month'));
        } else {
            $where = array('uid' => $uid, 'status' => array(1, 2));
        }
        // Check order ids
        if (!empty($orderIds)) {
            $where['order'] = $orderIds;
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

    public function cancelInvoiceFromOrder($order)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $where = array('order' => $order['id']);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $invoice) {
            $invoice->status = 0;
            $invoice->time_cancel = time();
            $invoice->save();
            // Update user credit
            if ($config['installment_credit'] && $order['type_payment'] == 'installment') {
                $message = __('Increase credit for cancel invoice');
                Pi::api('credit', 'order')->addCredit(Pi::user()->getId(), $invoice->credit_price, 'increase', 'automatic', $message, $message);
            }
        }
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
            $message = __('Increase credit for pay invoice');
            Pi::api('credit', 'order')->addCredit(Pi::user()->getId(), $invoice->credit_price, 'increase', 'automatic', $message, $message);
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
        // Set date_format
        $pattern = !empty($config['date_format']) ? $config['date_format'] : 'yyyy-MM-dd';
        // boject to array
        $invoice = $invoice->toArray();
        // Set time
        $invoice['time_create_view'] = _date($invoice['time_create'], array('pattern' => $pattern));
        $invoice['time_duedate_view'] = _date($invoice['time_duedate'], array('pattern' => $pattern));
        $invoice['time_payment_view'] = $invoice['time_payment'] ? _date($invoice['time_payment'], array('pattern' => $pattern)) : __('Not pay');
        $invoice['time_cancel_view'] = $invoice['time_cancel'] ? _date($invoice['time_cancel'], array('pattern' => $pattern)) : __('Not canceled');
        // Set price
        $invoice['product_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['product_price']);
        $invoice['shipping_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['shipping_price']);
        $invoice['discount_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['discount_price']);
        $invoice['packing_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['packing_price']);
        $invoice['setup_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['setup_price']);
        $invoice['vat_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['vat_price']);
        $invoice['total_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['total_price']);
        $invoice['paid_price_view'] = Pi::api('api', 'order')->viewPrice($invoice['paid_price']);
        // Set url
        $invoice['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'detail',
            'action' => 'index',
            'id' => $invoice['order'],
        )));
        $invoice['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'detail',
            'action' => 'index',
            'id' => $invoice['order'],
        )));
        $invoice['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'payment',
            'action' => 'index',
            'id' => $invoice['id'],
        )));
        $invoice['pay_credit_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'payment',
            'action' => 'index',
            'id' => $invoice['id'],
            'credit' => 1,
        )));
        $invoice['print_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'invoice',
            'action' => 'print',
            'id' => $invoice['id'],
        )));
        // Set anonymous pay
        $invoice['anonymous_pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module' => $this->getModule(),
            'controller' => 'payment',
            'action' => 'index',
            'id' => $invoice['id'],
            'anonymous' => 1,
            'token' => 'TOKEN_KEY',
        )));

        // Set extra
        if (!empty($invoice['extra'])) {
            $invoice['extra'] = json::decode($invoice['extra'], true);
        }
        // return order
        return $invoice;
    }

    public function setBackUrl($id, $url)
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($id);
        $invoice->back_url = $url;
        $invoice->save();

        $log = array();
        $log['gateway'] = 'paypal';
        $log['value'] = Json::encode(array(12, $invoice->toArray()));
        Pi::api('log', 'order')->setLog($log);
    }

    public function getInvoiceScore($uid)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set point
        $pointDivision = $config['score_division'];
        $pointNegative = 0;
        $pointPositive = 0;
        $pointScore = array(
            'type',
            'amount',
        );
        // Select
        $where = array('uid' => $uid, 'status' => 1);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            if ($row->time_payment > ($row->time_duedate + 86400)) {

                // Negative
                $days = number_format(($row->time_payment / 86400) - (($row->time_duedate + 86400) / 86400));
                $point = ($days * $row->total_price);
                $amount = $point * $pointDivision;
                $pointNegative = $pointNegative + $amount;

                /* echo '<pre>';
                print_r(array(
                    'type' => 'Negative',
                    'price' => $row->total_price,
                    'day' => $days,
                    'point' => $point,
                    'division' => $pointDivision,
                    'amount' => $amount,
                ));
                echo '</pre>'; */

            } elseif ($row->time_duedate > ($row->time_payment + 86400)) {

                // Positive
                $days = number_format(($row->time_duedate  / 86400) - (($row->time_payment + 86400) / 86400));
                $point = ($days * $row->total_price);
                $amount = $point * $pointDivision;
                $pointPositive = $pointPositive + $amount;

                /* echo '<pre>';
                print_r(array(
                    'type' => 'Positive',
                    'price' => $row->total_price,
                    'day' => $days,
                    'point' => $point,
                    'division' => $pointDivision,
                    'amount' => $amount,
                ));
                echo '</pre>'; */
            }
        }

        if ($pointNegative > $pointPositive) {
            // Negative
            $pointScore['type'] = 'negative';
            $pointScore['amount']= ($pointNegative - $pointPositive);
        } elseif ($pointPositive > $pointNegative) {
            // Positive
            $pointScore['type'] = 'positive';
            $pointScore['amount'] = ($pointPositive - $pointNegative);
        } else {
            // Normal
            $pointScore['type'] = 'normal';
            $pointScore['amount'] = 0;
        }

        return $pointScore;
    }
}
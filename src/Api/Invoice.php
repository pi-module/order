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
 * Pi::api('invoice', 'order')->generatCode();
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
    public function createInvoice($id, $uid = null, $admin = false)
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
             // Set invoice
            $row = Pi::model('invoice', $this->getModule())->createRow();
            $row->code = Pi::api('invoice', 'order')->generatCode();
            $row->random_id = time() + rand(100, 999);
            $row->status = \Module\Order\Model\Invoice::STATUS_INVOICE_DRAFT;
            $row->time_create = $order['time_create'];
            $row->time_duedate = time();
            $row->order = $order['id'];
            
            //$row->credit_price = 0;
            $row->gateway = $order['gateway'];
            if ($admin) {
                $row->create_by = 'ADMIN';
            }
            $row->save();
           
            // return array
            $result['status'] = $row->status;
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
        }

        $result['random_id'] = $row->random_id;
        return $result;
    }

    public function generatCode()
    {
        $config = Pi::service('registry')->config->read($this->getModule());
                    
        $year = date('Y');
        $count = Pi::model('invoice', 'order')->count(array('time_create >= ' . strtotime('01-01-' . $year)));
        $num = $year .  sprintf('%03d', ($count+1));  
        
        return sprintf('%s-%s', $config['invoice_code_prefix'], $num);
      
    }

    public function getInvoice($parameter, $type = 'id')
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($parameter, $type);
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function getInvoiceFromOrder($orderId, $getLog = true)
    {
        $invoices = array();
        $where = array('order' => $orderId);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoices[$row->id] = $this->canonizeInvoice($row);
        }
        return $invoices;
    }

    public function getInvoiceFromUser($uid, $compressed = false, $orderIds = array())
    {
        $invoices = array();
        // Check compressed
        if ($compressed) {
            $where = array('order.uid' => $uid, 'invoice.status' => 2, 'invoice.time_duedate < ?' => strtotime('+1 month'));
        } else {
            $where = array('order.uid' => $uid, 'invoice.status' => array(1, 2));
        }
        // Check order ids
        if (!empty($orderIds)) {
            $where['invoice.order'] = $orderIds;
        }
        
        $invoiceTable = Pi::model('invoice', 'order')->getTable();
        $orderTable = Pi::model("order", 'order')->getTable();
     
        $select = Pi::db()->select();
        $select
        ->from(array('invoice' => $invoiceTable))
        ->join(array('order' => $orderTable), 'invoice.order = order.id', array())
        ->where ($where);
        
        $rowset = Pi::db()->query($select);
        foreach ($rowset as $row) {
            $invoices[$row['id']] = $this->canonizeInvoice($row);
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
            if ($config['installment_credit'] && $invoice->type_payment == 'installment') {
                $message = __('Increase credit for cancel invoice');
                //Pi::api('credit', 'order')->addCredit(Pi::user()->getId(), $invoice->credit_price, 'increase', 'automatic', $message, $message);
            }
        }
    }

    public function updateInvoice($randomId, $gateway = '')
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $invoice = Pi::model('invoice', $this->getModule())->find($randomId, 'random_id');
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Update invoice
        $invoice->status = \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED;
        $invoice->save();
        $this->createInstallments($invoice->toArray(), true, $gateway);
        
        // Update user credit
        if ($config['installment_credit'] && $invoice->type_payment == 'installment') {
            $message = __('Increase credit for pay invoice');
            //Pi::api('credit', 'order')->addCredit(Pi::user()->getId(), $invoice->credit_price, 'increase', 'automatic', $message, $message);
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
        if (!is_array($invoice)) {
            $invoice = $invoice->toArray();
        }
        // Set time
        $invoice['time_create_view'] = _date($invoice['time_create'], array('pattern' => $pattern));
        $invoice['time_cancel_view'] = $invoice['time_cancel'] ? _date($invoice['time_cancel'], array('pattern' => $pattern)) : __('Not canceled');
       
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

        // return order
        return $invoice;
    }

    public function setBackUrl($id, $url)
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($id);
        $invoice->back_url = $url;
        $invoice->save();
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
        $where = array('order.uid' => $uid, 'invoice.status' => 1);
        
        $invoiceTable = Pi::model('invoice', 'order')->getTable();
        $orderTable = Pi::model("order", 'order')->getTable();
     
        $select = Pi::db()->select();
        $select
        ->from(array('invoice' => $invoiceTable))
        ->join(array('order' => $orderTable), 'invoice.order = order.id', array())
        ->where ($where);
        
        $rowset = Pi::db()->query($select);
        foreach ($rowset as $row) {
            if ($row['time_payment'] > ($row['time_duedate'] + 86400)) {

                // Negative
                $days = number_format(($row['time_payment'] / 86400) - (($row['time_duedate'] + 86400) / 86400));
                $point = ($days * $row['total_price']);
                $amount = $point * $pointDivision;
                $pointNegative = $pointNegative + $amount;

            } elseif ($row['time_duedate'] > ($row['time_payment'] + 86400)) {

                // Positive
                $days = number_format(($row['time_duedate']  / 86400) - (($row['time_payment'] + 86400) / 86400));
                $point = ($days * $row['total_price']);
                $amount = $point * $pointDivision;
                $pointPositive = $pointPositive + $amount;
            }
        }

        if ($pointNegative > $pointPositive) {
            // Negative
            $pointScore['type'] = 'negative';
            $pointScore['type_view'] = __('Negative score');
            $pointScore['amount']= ($pointNegative - $pointPositive);
            $pointScore['amount_view'] = Pi::api('api', 'order')->viewPrice(($pointNegative - $pointPositive));
        } elseif ($pointPositive > $pointNegative) {
            // Positive
            $pointScore['type'] = 'positive';
            $pointScore['type_view'] = __('Positive score');
            $pointScore['amount'] = Pi::api('api', 'order')->viewPrice(($pointPositive - $pointNegative));
            $pointScore['amount_view'] = ($pointPositive - $pointNegative);
        } else {
            // Normal
            $pointScore['type'] = 'normal';
            $pointScore['type_view'] = __('Normal score');
            $pointScore['amount'] = 0;
            $pointScore['amount_view'] = Pi::api('api', 'order')->viewPrice(0);
        }

        return $pointScore;
    }

    public function pdf($id, $controls = true)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get order
        
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Check order
        if (empty($order)) {
            return array(
                'status' => 0,
                'message' => __('The order not found.')
            );
        }
        // Check order is for this user
        if ($controls) {
            if ($order['uid'] != Pi::user()->getId()) {
                return array(
                    'status' => 0,
                    'message' => __('This is not your order.')
                );
            }
            // Check order is for this user
            if ($order['status_order'] != \Module\Order\Model\Order::STATUS_ORDER_VALIDATED) {
                return array(
                    'status' => 0,
                    'message' => __('This order not active.')
                );
            }
        }

        // set Products
        $options = array(
            'credit' => $invoice['type'] == 'CREDIT',
            'invoice' => $id,
            'time_create' => $invoice['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED ? $invoice['time_cancel'] : time() 
        );
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $options);
        foreach ($order['products'] as $key => $product) {
            $order['total_product_price'] += $product['product_price'] - $product['discount_price']; 
            $order['total_shipping_price'] += $product['shipping_price'];
            $order['total_packing_price'] += $product['packing_price'];
            $order['total_setup_price'] += $product['setup_price'];
            $order['total_vat_price'] += $product['vat_price'];
            $unconsumedPrice = json_decode($product['extra'], true)['unconsumedPrice'];
            $order['total_unconsommed_price'] += $unconsumedPrice ?: 0;
        }
        
        $order['total_product_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_product_price']); 
        $order['total_shipping_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_shipping_price']);
        $order['total_packing_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_packing_price']);
        $order['total_setup_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_setup_price']);
        $order['total_vat_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_vat_price']);
        $order['total_unconsommed_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_unconsommed_price']);
        $order['total_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_product_price'] + $order['total_shipping_price'] + $order['total_packing_price'] + $order['total_setup_price'] + $order['total_vat_price'] - $order['total_discount_price'] - $order['total_unconsommed_price']); 

        // set Products
        $order['invoice'] = $invoice;
        // set delivery information
        $order['deliveryInformation'] = '';
        if ($order['delivery'] > 0 && $order['location'] > 0) {
            $order['deliveryInformation'] = Pi::api('delivery', 'order')->getDeliveryInformation($order['location'], $order['delivery']);
        }

        $installments = Pi::api('installment', 'order')->getInstallmentsFromInvoice($id);
        $order['status_payment'] = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID;
        foreach ($installments as $installment) {
            if ($installment['status_payment'] ==  \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID) {
                $order['status_payment'] = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID;
                break;        
            }
        }
        
        if ($order['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID) {
            $installment = current($installments);
            $order['time_payment_view'] = _date($installment['time_payment']);
        }
        
        $gateway = array();
        foreach ($installments as $installment) {
            $gateway[] = $installment['gateway'];         
            
        }
        $order['gateway'] = join(', ', $gateway);
        
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $template = 'order:front/print';
        $data = array('order' => $order, 'address' => $address, 'config' => $config);
        
        $name = sprintf("%s-%s.pdf", $config['order_filename_prefix'], $invoice['code']);
        Pi::service('html2pdf')->pdf($template, $data, $name);
    }

    public function generateCreditInvoice($invoice) {
        $row = Pi::model('invoice', 'order')->createRow();
        $row->code = Pi::api('invoice', 'order')->generatCode();
        $row->random_id = time() + rand(100, 999);
        $row->status = \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED;
        $row->time_create = time();
        $row->order = $invoice['order'];
        $row->create_by = 'ADMIN';
        $row->type = 'CREDIT';
        $row->save(); 
        
        $products = Pi::api('order', 'order')->listProduct($invoice['order']);
        $totalPrice = 0;
        foreach ($products as $product) {
            if ($product['module'] == 'order' && $product['product_type'] == 'credit') {
                continue;
            }
        
            $totalPrice += $product['product_price'] + $product['vat_price'] + $product['setup_price'] + $product['packing_price'] + $product['shipping_price'] - $product['discount_price'];
        }
        
        $detail = Pi::model('detail', 'order')->createRow();
        $detail->order = $invoice['order'];
        $detail->module = 'order';
        $detail->product = 0;
        $detail->product_type = 'credit';
        $detail->discount_price = 0;
        $detail->shipping_price = 0;
        $detail->packing_price = 0;
        $detail->setup_price = 0;
        $detail->vat_price = 0;
        $detail->product_price = -$totalPrice;
        $detail->number = 1;
        $detail->extra = json_encode(array('invoice' => $row->id));
        $detail->save();
        
        $installment = Pi::model('invoice_installment', 'order')->createRow();
        $installment->count = 1;
        $installment->gateway = 'manual';
        $installment->status_payment = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID;
        $installment->due_price = -$totalPrice;
        $installment->invoice = $row->id;
        $installment->time_duedate = time();
        $installment->save();
               
    }
    public function createInstallments($invoice, $paid = false, $gateway = '')
    {
        // Find due price
        $products = Pi::api('order', 'order')->listProduct($invoice['order']);
        $duePrice = 0;
        foreach ($products as $product) {
            $duePrice += $product['product_price'] - $product['discount_price'] + $product['shipping_price'] + $product['packing_price'] + $product['setup_price'] + $product['vat_price'];      
        }
        
        $invoiceInstallment = Pi::model('invoice_installment', 'order')->createRow();
        $installment = array(
            'invoice' => $invoice['id'],
            'count' => 1,
            'gateway' => $gateway ,
            'status_payment' => $paid ? \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID : \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID,
            'time_payment' => $paid ? time() : 0,
            'due_price' => $duePrice,
        );
        
        $invoiceInstallment->assign($installment);
        $invoiceInstallment->save();
    }
}

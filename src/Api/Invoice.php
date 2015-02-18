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
 * Pi::api('invoice', 'order')->createInvoice($order);
 * Pi::api('invoice', 'order')->getInvoice($id);
 * Pi::api('invoice', 'order')->getInvoiceFromItem($module, $part, $item);
 * Pi::api('invoice', 'order')->getInvoiceRandomId($id);
 * Pi::api('invoice', 'order')->listOrderInvoice($order);
 * Pi::api('invoice', 'order')->updateInvoice($id);
 * Pi::api('invoice', 'order')->updateModuleInvoice($id);
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
            // Check order type
            switch ($order['type']) {
                case 'free':
                    // Set invoice
                    $row = Pi::model('invoice', $this->getModule())->createRow();
                    $row->random_id = time();
                    $row->uid = $uid;
                    $row->ip = Pi::user()->getIp();
                    $row->status = 1;
                    $row->time_create = time();
                    $row->order = $order['id'];
                    $row->product_price = $order['product_price'];
                    $row->discount_price = $order['discount_price'];
                    $row->shipping_price = $order['shipping_price'];
                    $row->packing_price = $order['packing_price'];
                    $row->vat_price = $order['vat_price'];
                    $row->total_price = $order['total_price'];
                    $row->paid_price = $order['paid_price'];
                    $row->gateway = $order['gateway'];
                    $row->save();
                    break;
            
                case 'onetime':
                case 'recurring':
                    // Set invoice
                    $row = Pi::model('invoice', $this->getModule())->createRow();
                    $row->random_id = time();
                    $row->uid = $uid;
                    $row->ip = Pi::user()->getIp();
                    $row->status = 2;
                    $row->time_create = time();
                    $row->order = $order['id'];
                    $row->product_price = $order['product_price'];
                    $row->discount_price = $order['discount_price'];
                    $row->shipping_price = $order['shipping_price'];
                    $row->packing_price = $order['packing_price'];
                    $row->vat_price = $order['vat_price'];
                    $row->total_price = $order['total_price'];
                    $row->paid_price = $order['paid_price'];
                    $row->gateway = $order['gateway'];
                    $row->save();
                    // return array
                    $result['status'] = $row->status;
                    $result['message'] = __('Your invoice create successfully');
                    $result['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module'        => $this->getModule(),
                        'action'        => 'detail',
                        'id'            => $row->order,
                    )));
                    $result['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module'        => $this->getModule(),
                        'action'        => 'invoice',
                        'id'            => $row->id,
                    )));
                    $result['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                        'module'        => $this->getModule(),
                        'action'        => 'pay',
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

                    break;  
            }
        }
        // return
        return $result;
    }

    public function getInvoice($id)
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($id);
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function getInvoiceRandomId($id)
    {
        $rand = Rand::getInteger(10, 99);
        $invoice = array();
        $row = Pi::model('invoice', $this->getModule())->find($id);
        if (is_object($row)) {
            $row->random_id = sprintf('%s%s', $row->id, $rand);
            $row->save();
            $invoice = $this->canonizeInvoice($row);
        }
        return $invoice;
    }

    public function getInvoiceFromItem($module, $part, $item)
    {
        $invoice = array();

        $where = array('module' => $module, 'part' => $part, 'item' => $item);
        $select = Pi::model('invoice', $this->getModule())->select()->where($where)->limit(1);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select)->current();
        if (is_object($rowset)) {
            $invoice = $this->canonizeInvoice($rowset);
            $invoice['log'] = Pi::api('log', 'order')->getTrueLog($invoice['id']);
        }
        return $invoice;
    }

    public function listOrderInvoice($order)
    {
        $invoice = array();
        return $invoice;
    }

    public function updateInvoice($id)
    {
        $invoice = array();
        $row = Pi::model('invoice', $this->getModule())->find($id, 'random_id');
        if (is_object($row)) {
            $row->status = 1;
            $row->time_payment = time();
            $row->save();
            $invoice = $this->canonizeInvoice($row);
        }
        return $invoice;
    }

    public function updateModuleInvoice($id)
    {
        $invoice = $this->getInvoice($id);
        return Pi::api($invoice['part'], $invoice['module'])->updatePayment(
            $invoice['item'], 
            $invoice['amount'], 
            $invoice['gateway']);
    }

    public function setBackUrl($id, $url)
    {
        $row = Pi::model('invoice', $this->getModule())->find($id);
        $row->back_url = $url;
        $row->save();
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
        $invoice['time_create_view'] = _date($invoice['time_create']);
        $invoice['time_payment_view'] = _date($invoice['time_payment']);
        $invoice['time_cancel_view'] = _date($invoice['time_cancel']);
        // Set order id
        $invoice['order_view'] = _number($invoice['order']);
        // Set price
        $invoice['product_price_view'] = _currency($invoice['product_price']);
        $invoice['shipping_price_view'] = _currency($invoice['shipping_price']);
        $invoice['packing_price_view'] = _currency($invoice['packing_price']);
        $invoice['vat_price_view'] = _currency($invoice['vat_price']);
        $invoice['total_price_view'] = _currency($invoice['total_price']);
        $invoice['paid_price_view'] = _currency($invoice['paid_price']);
        // Set url
        $invoice['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'action'        => 'invoice',
            'id'            => $rowset->id,
        )));
        $invoice['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'action'        => 'pay',
            'id'            => $invoice['id'],
        )));
        $invoice['order_url'] = Pi::url(Pi::service('url')->assemble('order', array(
            'module'        => $this->getModule(),
            'action'        => 'detail',
            'id'            => $invoice['order'],
        )));
        // return order
        return $invoice; 
    }
}	
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
 * Pi::api('invoice', 'order')->createInvoice($module, $part, $item, $amount, $adapter, $description);
 * Pi::api('invoice', 'order')->createPaidInvoice($uid, $module, $part, $item, $amount, $adapter, $description);
 * Pi::api('invoice', 'order')->getInvoice($id);
 * Pi::api('invoice', 'order')->getInvoiceFromItem($module, $part, $item);
 * Pi::api('invoice', 'order')->getInvoiceRandomId($id);
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
    public function createInvoice($module, $part, $item, $amount, $adapter, $description)
    {
    	$result = array();
    	$uid = Pi::user()->getId();
        $config = Pi::service('registry')->config->read($this->getModule());
        // Check user
        if ($config['payment_anonymous'] == 0 && $uid == 0) {
            $result['status'] = 0;
            $result['pay_url'] = '';
            $result['message'] = __('Please login for create invoice');
        } else {
            if (empty($module) || 
                empty($part) || 
                empty($item) || 
                empty($amount) || 
                empty($adapter) || 
                empty($description)) 
            {
                $result['status'] = 0;
                $result['pay_url'] = '';
                $result['message'] = __('Please send all informations for create invoice');
            } else {
                // create invoice
                $row = Pi::model('invoice', $this->getModule())->createRow();
                $row->random_id = time();
                $row->module = $module;
                $row->part = $part;
                $row->item = $item;
                $row->amount = $amount;
                $row->adapter = $adapter;
                $row->description = $description;
                $row->uid = $uid;
                $row->ip = Pi::user()->getIp();
                $row->status = 2;
                $row->time_create = time();
                $row->save();
                // return array
                $result['status'] = $row->status;
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
                $result['message'] = __('Your invoice create successfully');
                // Set invoice information on session
                if ($config['payment_anonymous'] == 1) {
                    $_SESSION['order']['process'] = 1;
                    $_SESSION['order']['process_start'] = time();
                    $_SESSION['order']['invoice_id'] = $row->id;
                    $_SESSION['order']['adapter'] = $adapter;
                }
            }
        }
    	return $result;
    }

    public function createPaidInvoice($uid, $module, $part, $item, $amount, $adapter, $description)
    {
        // create invoice
        $row = Pi::model('invoice', $this->getModule())->createRow();
        $row->random_id = time();
        $row->module = $module;
        $row->part = $part;
        $row->item = $item;
        $row->amount = $amount;
        $row->adapter = $adapter;
        $row->description = $description;
        $row->uid = $uid;
        $row->ip = Pi::user()->getIp();
        $row->status = 1;
        $row->time_create = time();
        $row->save();
    }

    public function getInvoice($id)
    {
        $invoice = array();
        $row = Pi::model('invoice', $this->getModule())->find($id);
        if (is_object($row)) {
            $invoice = $row->toArray();
            $invoice['description'] = Json::decode($invoice['description'], true);
            $invoice['time_create_view'] = _date($invoice['time_create']);
            $invoice['amount_view'] = _currency($invoice['amount']);
            $invoice['item_view'] = _number($invoice['item']);
            $invoice['pay'] = Pi::url(Pi::service('url')->assemble('order', array(
                'module'        => $this->getModule(),
                'action'        => 'pay',
                'id'            => $invoice['id'],
            )));
        }
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
            $invoice = $row->toArray();
            $invoice['description'] = Json::decode($invoice['description'], true);
            $invoice['create'] = _date($invoice['time_create']);
            $invoice['pay'] = Pi::url(Pi::service('url')->assemble('order', array(
                'module'        => $this->getModule(),
                'action'        => 'pay',
                'id'            => $invoice['id'],
            )));
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
            $invoice = $rowset->toArray();
            $invoice['description'] = Json::decode($invoice['description'], true);
            $invoice['create'] = _date($invoice['time_create']);
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
            $invoice['log'] = Pi::api('log', 'order')->getTrueLog($invoice['id']);
        }
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
            $invoice = $row->toArray();
        }
        return $invoice;
    }

    public function updateModuleInvoice($id)
    {
        $invoice = $this->getInvoice($id);
        return Pi::api($invoice['part'], $invoice['module'])->updatePayment(
            $invoice['item'], 
            $invoice['amount'], 
            $invoice['adapter']);
    }

    public function setBackUrl($id, $url)
    {
        $row = Pi::model('invoice', $this->getModule())->find($id);
        $row->back_url = $url;
        $row->save();
    }
}	
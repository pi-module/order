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
 * Pi::api('customer', 'order')->addCustomer($values);
 * Pi::api('customer', 'order')->updateCustomer($values);
 * Pi::api('customer', 'order')->findCustomer($uid);
 * Pi::api('customer', 'order')->canonizeCustomer($customer);
 */

class Customer extends AbstractApi
{
    public function addCustomer($values)
    {
        // Set values
        $values['time_update'] = $values['time_create'];
        $values['state'] = 1;
        // Save customer info
        $customer = Pi::model('customer', $this->getModule())->createRow();
        $customer->assign($values);
        $customer->save();
        // return
        $customer = $this->canonizeCustomer($customer);
        return $customer;

    }
    public function updateCustomer($values)
    {
        // Set values
        $values['time_update'] = time();
        $values['state'] = 1;
        // Find customer info
        $customer = Pi::model('customer', $this->getModule())->find($values['customer_id']);
        // Check customer
        if ($customer->uid != Pi::user()->getId()) {
        	return false;
        } else {
            // Save customer info
            $customer->assign($values);
            $customer->save();
            // return
            $customer = $this->canonizeCustomer($customer);
            return $customer;
        }
    }

    public function findCustomer($uid = '')
    {
    	// Check uid
    	if (empty($uid)) {
    		$uid = Pi::user()->getId();
    	}
        // Select
        $customers = array();
        $where = array('uid' => $uid);
        $select = Pi::model('customer', $this->getModule())->select()->where($where);
        $rowset = Pi::model('customer', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $customers[$row->id] = $this->canonizeCustomer($row);
        }
        // return
        return $customers;
    }

    public function canonizeCustomer($customer)
    {
        // Check
        if (empty($customer)) {
            return '';
        }
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set date_format
        $pattern = !empty($config['date_format']) ? $config['date_format'] : 'yyyy-MM-dd';
        // boject to array
        $customer = $customer->toArray();
        // Set customer id
        $customer['customer_id'] = $customer['id'];
        // Set time
        $customer['time_create_view'] = _date($customer['time_create'], array('pattern' => $pattern));
        $customer['time_update_view'] = _date($customer['time_update'], array('pattern' => $pattern));
        // customer
        return $customer;
    }
}
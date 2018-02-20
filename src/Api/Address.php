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
 * Pi::api('address', 'order')->updateFavouriteDelivery($id);
 * Pi::api('address', 'order')->updateFavouriteInvoicing($id);
 * Pi::api('address', 'order')->getFavouriteDelivery();
 * Pi::api('address', 'order')->getFavouriteInvoicing();
 * Pi::api('address', 'order')->addAddress($values);
 * Pi::api('address', 'order')->updateAddress($values);
 * Pi::api('address', 'order')->getdAddress();
 * Pi::api('address', 'order')->findAddresses($uid);
 * Pi::api('address', 'order')->canonizeAddress($address);
 */

class Address extends AbstractApi
{
    public function updateFavouriteDelivery($id)
    {
        Pi::model('address', 'order')->update(
            array('delivery_favourite' => 0),
            array('uid' => $uid)
        );
        
        Pi::model('address', 'order')->update(
            array('delivery_favourite' => 1),
            array('uid' => $uid, 'id' => $id)
        );
    }
    
    public function updateFavouriteInvoicing($id)
    {
        Pi::model('address', 'order')->update(
            array('invoicing_favourite' => 0),
            array('uid' => $uid)
        );
        
        Pi::model('address', 'order')->update(
            array('invoicing_favourite' => 1),
            array('uid' => $uid, 'id' => $id)
        );
    }
    
    public function getFavouriteDelivery()
    {
        // Check uid
        $uid = Pi::user()->getId();
       
        // Select
        $addresss = array();
        $where = array('uid' => $uid, 'delivery_favourite' => 1);
        $select = Pi::model('address', $this->getModule())->select()->where($where)->order('id DESC');
        $row = Pi::model('address', $this->getModule())->selectWith($select)->current();
        if ($row) {
            $address = $this->canonizeAddress($row);
            return $address;
        }
        return array();
    }
    public function getFavouriteInvoicing()
    {
        // Check uid
        $uid = Pi::user()->getId();

        // Select
        $addresss = array();
        $where = array('uid' => $uid, 'invoicing_favourite' => 1);
        $select = Pi::model('address', $this->getModule())->select()->where($where)->order('id DESC');
        $row = Pi::model('address', $this->getModule())->selectWith($select)->current();
        if ($row) {
            $address = $this->canonizeAddress($row);
            return $address;
        }
        return array();
    }
    
    
    public function addAddress($values)
    {
        // Set values
        $values['time_update'] = $values['time_create'];
        $values['status'] = 1;
        unset($values['user_note']);
        // Save address info
        $address = Pi::model('address', $this->getModule())->createRow();
        $address->assign($values);
        $address->save();
        // return
        $address = $this->canonizeAddress($address);
        return $address;

    }

    public function updateAddress($values)
    {
        // Set values
        $values['time_update'] = time();
        $values['status'] = 1;
        unset($values['user_note']);
        // Find address info
        $address = Pi::model('address', $this->getModule())->find($values['address_id']);
        // Check address
        if ($address->uid != Pi::user()->getId()) {
            return false;
        } else {
            // Save address info
            $address->assign($values);
            $address->save();
            // return
            $address = $this->canonizeAddress($address);
            return $address;
        }
    }
    
    public function getAddress($id)
    {
        // Check uid
        if (empty($uid)) {
            $uid = Pi::user()->getId();
        }
        
        $row = Pi::model('address', 'order')->find($id, 'id');
        if ($row->uid != $uid) {
            return array();
        }
        
        // return
        return $this->canonizeAddress($row);
    }

    public function findAddresses($uid = '')
    {
        // Check uid
        if (empty($uid)) {
            $uid = Pi::user()->getId();
        }
        // Select
        $addresses = array();
        $where = array('uid' => $uid);
        $select = Pi::model('address', $this->getModule())->select()->where($where)->order('id DESC');
        $rowset = Pi::model('address', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $addresses[$row->id] = $this->canonizeAddress($row);
        }
        // return
        return $addresses;
    }

    public function canonizeAddress($address)
    {
        // Check
        if (empty($address)) {
            return '';
        }
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set date_format
        $pattern = !empty($config['date_format']) ? $config['date_format'] : 'yyyy-MM-dd';
        // boject to array
        $address = $address->toArray();
        // Set address id
        $address['address_id'] = $address['id'];
        // Set time
        $address['time_create_view'] = _date($address['time_create'], array('pattern' => $pattern));
        $address['time_update_view'] = _date($address['time_update'], array('pattern' => $pattern));
        // address
        return $address;
    }
}
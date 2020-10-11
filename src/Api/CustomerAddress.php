<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('customerAddress', 'order')->updateFavouriteDelivery($id);
 * Pi::api('customerAddress', 'order')->updateFavouriteInvoicing($id);
 * Pi::api('customerAddress', 'order')->getFavouriteDelivery();
 * Pi::api('customerAddress', 'order')->getFavouriteInvoicing();
 * Pi::api('customerAddress', 'order')->addAddress($values);
 * Pi::api('customerAddress', 'order')->updateAddress($values);
 * Pi::api('customerAddress', 'order')->getdAddress();
 * Pi::api('customerAddress', 'order')->findAddresses($uid);
 * Pi::api('customerAddress', 'order')->canonizeAddress($address);
 */

class CustomerAddress extends AbstractApi
{
    public function updateFavouriteDelivery($id)
    {
        // Get uid
        $uid = Pi::user()->getId();

        Pi::model('customer_address', 'order')->update(
            ['delivery_favourite' => 0],
            ['uid' => $uid]
        );

        Pi::model('customer_address', 'order')->update(
            ['delivery_favourite' => 1],
            ['uid' => $uid, 'id' => $id]
        );
    }

    public function updateFavouriteInvoicing($id)
    {
        // Get uid
        $uid = Pi::user()->getId();

        Pi::model('customer_address', 'order')->update(
            ['invoicing_favourite' => 0],
            ['uid' => $uid]
        );

        Pi::model('customer_address', 'order')->update(
            ['invoicing_favourite' => 1],
            ['uid' => $uid, 'id' => $id]
        );
    }

    public function getFavouriteDelivery()
    {
        // Check uid
        $uid = Pi::user()->getId();

        // Select
        $where  = ['uid' => $uid, 'delivery_favourite' => 1];
        $select = Pi::model('customer_address', $this->getModule())->select()->where($where)->order('id DESC');
        $row    = Pi::model('customer_address', $this->getModule())->selectWith($select)->current();
        if ($row) {
            $address = $this->canonizeAddress($row);
            return $address;
        }
        return [];
    }

    public function getFavouriteInvoicing()
    {
        // Check uid
        $uid = Pi::user()->getId();

        // Select
        $addresss = [];
        $where    = ['uid' => $uid, 'invoicing_favourite' => 1];
        $select   = Pi::model('customer_address', $this->getModule())->select()->where($where)->order('id DESC');
        $row      = Pi::model('customer_address', $this->getModule())->selectWith($select)->current();
        if ($row) {
            $address = $this->canonizeAddress($row);
            return $address;
        }
        return [];
    }

    public function addAddress($values)
    {
        // Set values
        $values['time_update'] = $values['time_create'];
        $values['status']      = 1;
        unset($values['user_note']);
        // Save address info

        $address = Pi::model('customer_address', $this->getModule())->createRow();
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
        $values['status']      = 1;
        unset($values['user_note']);
        // Find address info
        $address = Pi::model('customer_address', $this->getModule())->find($values['address_id']);
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
        // Get uid
        $uid = Pi::user()->getId();

        $row = Pi::model('customer_address', 'order')->find($id, 'id');
        if (!$row || $row->uid != $uid) {

            return [];
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
        $addresses = [];
        $where     = ['uid' => $uid];
        $select    = Pi::model('customer_address', $this->getModule())->select()->where($where)->order('id DESC');
        $rowset    = Pi::model('customer_address', $this->getModule())->selectWith($select);
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
        $address['time_create_view'] = _date($address['time_create'], ['pattern' => $pattern]);
        $address['time_update_view'] = _date($address['time_update'], ['pattern' => $pattern]);
        // address
        return $address;
    }
}

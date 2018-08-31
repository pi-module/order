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
 * Pi::api('processing', 'order')->setProcessing($invoice);
 * Pi::api('processing', 'order')->getProcessing($random_id);
 * Pi::api('processing', 'order')->checkProcessing();
 * Pi::api('processing', 'order')->removeProcessing($invoice);
 */

class Processing extends AbstractApi
{
    public function setProcessing($order, $gateway)
    {
        $rand = Rand::getInteger(10, 99);
         
        // create processing
        $row = Pi::model('processing', $this->getModule())->createRow();
        $row->uid = Pi::user()->getId();
        $row->ip = Pi::user()->getIp();
        $row->order = $order['id'];
        $row->random_id = sprintf('%s%s', $order['id'], $rand);
        $row->gateway = $gateway;
        $row->time_create = time();
        $row->save();
    }

    public function getProcessing($random_id = '')
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // get
        if (!empty($random_id)) {
            $row = Pi::model('processing', $this->getModule())->find($random_id, 'random_id');
        } else {
            $uid = Pi::user()->getId();
            if ($uid) {
                $row = Pi::model('processing', $this->getModule())->find($uid, 'uid');
            } elseif ($config['order_anonymous']) {
                $row = Pi::model('processing', $this->getModule())->find($_SESSION['order']['id'], 'order');
            }
        }
        // check
        if (is_object($row)) {
            $row = $row->toArray();
            return $row;
        } else {
            return false;
        }
    }

    public function checkProcessing()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get user id
        $uid = Pi::user()->getId();
        // Check user
        if ($uid) {
            $row = Pi::model('processing', $this->getModule())->find($uid, 'uid');
        } elseif ($config['order_anonymous']) {
            $row = Pi::model('processing', $this->getModule())->find($_SESSION['order']['id'], 'order');
        } else {
            return false;
        }
        // Check row
        if (is_object($row)) {
            $time = time() - 900;
            if ($time > $row->time_create) {
                $this->removeProcessing();
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function removeProcessing($random_id = '')
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // get
        if (!empty($random_id)) {
            $row = Pi::model('processing', $this->getModule())->find($random_id, 'random_id');
        } else {
            $uid = Pi::user()->getId();
            if ($uid) {
                $row = Pi::model('processing', $this->getModule())->find($uid, 'uid');
            } elseif ($config['order_anonymous']) {
                $row = Pi::model('processing', $this->getModule())->find($_SESSION['order']['id'], 'order');
            }
        }
        // delete
        if (!empty($row)) {
            $row->delete();
        }
    }
}
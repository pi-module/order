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

/*
 * Pi::api('processing', 'order')->setProcessing($invoice);
 * Pi::api('processing', 'order')->getProcessing($random_id);
 * Pi::api('processing', 'order')->checkProcessing();
 * Pi::api('processing', 'order')->removeProcessing($invoice);
 */

class Processing extends AbstractApi
{
    public function setProcessing($invoice)
    {
        // create processing
        $row = Pi::model('processing', $this->getModule())->createRow();
        $row->uid = Pi::user()->getId();
        $row->ip = Pi::user()->getIp();
        $row->invoice = $invoice['id'];
        $row->random_id = $invoice['random_id'];
        $row->gateway = $invoice['gateway'];
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
            if ($config['order_anonymous'] == 0) {
                $uid = Pi::user()->getId();
                $row = Pi::model('processing', $this->getModule())->find($uid, 'uid');
            } else {
                $invoice = $_SESSION['order']['invoice_id'];
                $row = Pi::model('processing', $this->getModule())->find($invoice, 'invoice');
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
        // Check config
        if ($config['order_anonymous'] == 0) {
            $uid = Pi::user()->getId();
            $row = Pi::model('processing', $this->getModule())->find($uid, 'uid');
        } else {
            $invoice = $_SESSION['order']['invoice_id'];
            $row = Pi::model('processing', $this->getModule())->find($invoice, 'invoice');
        }
    	// check row
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
            if ($config['order_anonymous'] == 0) {
                $uid = Pi::user()->getId();
                $row = Pi::model('processing', $this->getModule())->find($uid, 'uid');
            } else {
                $invoice = $_SESSION['order']['invoice_id'];
                $row = Pi::model('processing', $this->getModule())->find($invoice, 'invoice');
            }
        }
        // delete
        if (!empty($row)) {
            $row->delete();
        }
    }
}	
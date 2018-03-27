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
 * Pi::api('cron', 'order')->start();
 */

class Cron extends AbstractApi
{
    
    
    public function start()
    {
        // TODO - #1278
        /*
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        // Check cron active for this module
        if ($config['module_cron']) {

            // Set log
            Pi::service('audit')->log('cron', 'order - Start cron on server');

            // due date invoices
            $duedate1 = time();
            $duedate2 = time() + (86400 * intval($config['notification_cron_invoice']));
            $where = array('status' => 2, 'time_duedate > ?' => $duedate1, 'time_duedate < ?' => $duedate2);
            $select = Pi::model('invoice', $this->getModule())->select()->where($where);
            $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
            foreach ($rowset as $row) {
                $invoice = Pi::api('invoice', 'order')->canonizeInvoice($row);
                $order = Pi::api('order', 'order')->getOrder($row->order);
                Pi::api('notification', 'order')->duedateInvoice($order, $invoice);
                // Set log
                $audit = array('order - due date invoic' , json_encode($order), json_encode($invoice));
                Pi::service('audit')->log('cron', $audit);
            }

            // expired invoices
            $time = time() - 86400;
            $where = array('status' => 2, 'time_duedate < ?' => $time);
            $select = Pi::model('invoice', $this->getModule())->select()->where($where);
            $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
            foreach ($rowset as $row) {
                $invoice = Pi::api('invoice', 'order')->canonizeInvoice($row);
                $order = Pi::api('order', 'order')->getOrder($row->order);
                Pi::api('notification', 'order')->expiredInvoice($order, $invoice);
                // Set log
                $audit = array('order - expired invoice' , json_encode($order), json_encode($invoice));
                Pi::service('audit')->log('cron', $audit);
            }

            // Set log
            Pi::service('audit')->log('cron', 'order - End cron on server');

        } else {
            // Set log
            Pi::service('audit')->log('cron', 'order - cron system not active for this module');
        }
         
        */
    }
}
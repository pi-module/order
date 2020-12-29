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
 * Pi::api('access', 'order')->hasAccess($key, $clean);
 * Pi::api('access', 'order')->setAccess($access);
 * Pi::api('access', 'order')->deleteAccess($key);
 */

class Access extends AbstractApi
{
    public function hasAccess($key, $clean = false)
    {
        $access = Pi::model('access', $this->getModule())->find($key, 'item_key');
        if ($access) {
            $access = $access->toArray();
            // Check status
            if ($access['status'] != 1) {
                if ($clean) {
                    $access->delete();
                }
                return false;
            }
            // Check time
            if ($access['time_start'] > time() || $access['time_end'] < time()) {
                if ($clean) {
                    $access->delete();
                }
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function setAccess($access)
    {
        $row              = Pi::model('access', $this->getModule())->createRow();
        $row->uid         = isset($access['uid']) ? $access['uid'] : Pi::user()->getId();
        $row->ip          = Pi::user()->getIp();
        $row->time_create = time();
        $row->time_start  = isset($access['time_start']) ? $access['time_start'] : time();
        $row->time_end    = isset($access['time_end']) ? $access['time_end'] : strtotime('2030-01-01');
        $row->item_key    = $access['item_key'];
        $row->order       = isset($access['order']) ? $access['order'] : 0;
        $row->status      = isset($access['status']) ? $access['status'] : 1;
        $row->save();
        return $row->toArray();
    }

    public function deleteAccess($key)
    {
        $access = Pi::model('access', $this->getModule())->find($key, 'item_key');
        if ($access) {
            $access->delete();
        }
    }
}

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
        $row = Pi::model('access', $this->getModule())->createRow();
        $row->uid = Pi::user()->getId();
        $row->ip = Pi::user()->getIp();
        $row->time_create = time();
        $row->time_start = $access['time_start'];
        $row->time_end = $access['time_end'];
        $row->item_key = $access['item_key'];
        $row->order = $access['order'];
        $row->status = $access['status'];
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
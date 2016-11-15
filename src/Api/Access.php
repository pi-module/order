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
 * Pi::api('access', 'order')->checkAccess($key);
 * Pi::api('access', 'order')->setAccess();
 */

class Access extends AbstractApi
{
    public function checkAccess($key)
    {
        $access = Pi::model('access', $this->getModule())->find($key, 'item_key');
        if ($access) {
            $access = $access->toArray();
            // Check status
            if ($access['status'] != 1) {
                return false;
            }
            // Check time
            if ($access['time_start'] > time() || $access['time_end'] < time()) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function setAccess()
    {
        return true;
    }
}
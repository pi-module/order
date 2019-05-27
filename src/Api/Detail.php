<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('detail', 'order')->isActive($key, $clean);
 */

class Detail extends AbstractApi
{
    public function isActive($id)
    {
        $detail = Pi::model('detail', $this->getModule())->find($id, 'id');
        if ($detail) {
            if ($detail->time_start <= time() && $detail->time_end >= time()) {
                return true;
            }
        }
        return false;
    }
}
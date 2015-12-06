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
 * Pi::api('delivery', 'order')->getPrice($location, $delivery);
 */

class Delivery extends AbstractApi
{
    public function getPrice($location, $delivery)
    {
        // Get product
        $where = array('location' => $location, 'delivery' => $delivery);
        $select = Pi::model('location_delivery', $this->getModule())->select()->where($where)->limit(1);
        $row = Pi::model('location_delivery', $this->getModule())->selectWith($select)->current();
        return $row->price;
    }
}
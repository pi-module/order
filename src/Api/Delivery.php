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
 * Pi::api('delivery', 'order')->getDeliveryInformation($location, $delivery);
 * Pi::api('delivery', 'order')->getPrice($location, $delivery);
 */

class Delivery extends AbstractApi
{
    public function getDeliveryInformation($location, $delivery)
    {
        // Get product
        $where = array('location' => $location, 'delivery' => $delivery);
        $select = Pi::model('location_delivery', $this->getModule())->select()->where($where)->limit(1);
        $row = Pi::model('location_delivery', $this->getModule())->selectWith($select)->current()->toArray();
        $row['deliveryInfo'] = Pi::model('delivery', $this->getModule())->find($delivery)->toArray();
        $row['locationInfo'] = Pi::model('location', $this->getModule())->find($location)->toArray();
        // Set result
        $result = array(
            'price_view' => Pi::api('api', 'order')->viewPrice($row['price']),
            'delivery_time_view' => sprintf('%s Days', _number($row['delivery_time'])),
            'delivery_title' => $row['deliveryInfo']['title'],
            'location_title' => $row['locationInfo']['title'],
        );
        return $result;
    }

    public function getPrice($location, $delivery)
    {
        // Get product
        $where = array('location' => $location, 'delivery' => $delivery);
        $select = Pi::model('location_delivery', $this->getModule())->select()->where($where)->limit(1);
        $row = Pi::model('location_delivery', $this->getModule())->selectWith($select)->current();
        return $row->price;
    }
}
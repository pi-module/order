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
 * Pi::api('order', 'order')->checkoutConfig();
 * Pi::api('order', 'order')->generatCode();
 */

class Order extends AbstractApi
{
    public function checkoutConfig()
    {
        $return = array();
        // Set location
        $select = Pi::model('location', 'order')->select();
        $location = Pi::model('location', 'order')->selectWith($select)->toArray();
        $return['location'] = (empty($location)) ? 0 : 1;
        // Set delivery
        $select = Pi::model('delivery', 'order')->select();
        $delivery = Pi::model('delivery', 'order')->selectWith($select)->toArray();
        $return['delivery'] = (empty($delivery)) ? 0 : 1;
        // Set payment
        $payment = Pi::api('gateway', 'order')->getActiveGatewayList();
        $return['payment'] = (empty($payment)) ? 0 : 1;
        // return
        return $return;
    }

    public function generatCode()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        $prefix = $config['order_code_prefix'];
        // Generate random code
        $rand = Rand::getInteger(10000000, 99999999);
        // Generate order code
        $code = sprintf('%s-%s', $prefix, $rand);
        return $code;
    }
}	
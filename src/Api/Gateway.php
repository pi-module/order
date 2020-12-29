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

use Module\Order\Gateway\AbstractGateway;
use Pi;
use Pi\Application\Api\AbstractApi;

/*
 * Pi::api('gateway', 'order')->getAllGatewayList();
 * Pi::api('gateway', 'order')->getActiveGatewayList();
 * Pi::api('gateway', 'order')->getActiveGatewayName();
 * Pi::api('gateway', 'order')->getGateway($adapter);
 * Pi::api('gateway', 'order')->getGatewayInfo($adapter);
 * Pi::api('gateway', 'order')->getGatewayMessage($adapter, $log);
 */

class Gateway extends AbstractApi
{
    public function getAdminGatewayList()
    {
        $gatewaysList = $this->getActiveGatewayList();
        $gateways     = ['manual' => __('Manual')];
        foreach ($gatewaysList as $gateway) {
            $gateways[$gateway['path']] = $gateway['title'];
        }
        return $gateways;
    }

    public function getAllGatewayList()
    {
        return AbstractGateway::getAllList();
    }

    public function getActiveGatewayList()
    {
        return AbstractGateway::getActiveList();
    }

    public function getActiveGatewayName()
    {
        return AbstractGateway::getActiveName();
    }

    public function getGateway($adapter = '')
    {
        return AbstractGateway::getGateway($adapter);
    }

    public function getGatewayInfo($adapter = '')
    {
        return AbstractGateway::getGatewayInfo($adapter);
    }

    public function getGatewayMessage($adapter = '', $log = [])
    {
        return AbstractGateway::getGatewayMessage($adapter, $log);
    }
}

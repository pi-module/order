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
use Module\Order\Gateway\AbstractGateway;

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

    public function getGatewayMessage($adapter = '', $log = array())
    {
        return AbstractGateway::getGatewayMessage($adapter, $log);
    }
}	
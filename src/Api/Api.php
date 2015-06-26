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
 * Pi::api('api', 'order')->viewPrice($price, $short);
 * Pi::api('api', 'order')->makePrice($price);
 */

class Api extends AbstractApi
{
    public function viewPrice($price, $short = false)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Check custom price
        if ($config['price_custom']) {
            switch (Pi::config('number_currency')) {
                // Set for Iran Rial
                case 'IRR':
                    $price = ((int)($price / 1000)) * 100;
                    $viewPrice = $short ? _number($price) : sprintf('%s %s', _number($price), __('Toman'));
                    break;

                default:
                    $viewPrice = _currency($price);
                    break;
            }
        } else {
            $viewPrice = _currency($price);
        }
        return $viewPrice;
    }

    public function makePrice($price)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Check custom price
        if ($config['price_custom']) {
            switch (Pi::config('number_currency')) {
                // Set for Iran Rial
                case 'IRR':
                    $price = ((int)($price / 1000)) * 1000;
                    break;
            }
        }
        return $price;
    }
}
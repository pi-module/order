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
return [
    // route name
    'order' => [
        'name'    => 'order',
        'type'    => 'Module\Order\Route\Order',
        'options' => [
            'route'    => '/order',
            'defaults' => [
                'module'     => 'order',
                'controller' => 'index',
                'action'     => 'index',
            ],
        ],
    ],
];

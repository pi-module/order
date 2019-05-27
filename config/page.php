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
    // Admin section
    'admin' => [
        [
            'label'      => _a('Orders'),
            'controller' => 'order',
            'permission' => 'order',
        ],
        [
            'label'      => _a('Invoice'),
            'controller' => 'invoice',
            'permission' => 'invoice',
        ],
        [
            'label'      => _a('Logs'),
            'controller' => 'logs',
            'permission' => 'logs',
        ],
        [
            'label'      => _a('Gateway'),
            'controller' => 'gateway',
            'permission' => 'gateway',
        ],
        [
            'label'      => _a('Delivery'),
            'controller' => 'delivery',
            'permission' => 'delivery',
        ],
        [
            'label'      => _a('Location'),
            'controller' => 'location',
            'permission' => 'location',
        ],
        [
            'label'      => _a('Credit'),
            'controller' => 'credit',
            'permission' => 'credit',
        ],
    ],
    // Front section
    'front' => [
        [
            'title'      => _a('Index page'),
            'controller' => 'index',
            'permission' => 'public',
            'block'      => 1,
        ],
        [
            'title'      => _a('Checkout'),
            'controller' => 'checkout',
            'permission' => 'public',
            'block'      => 1,
        ],
        [
            'title'      => _a('Detail'),
            'controller' => 'detail',
            'permission' => 'public',
            'block'      => 1,
        ],
        [
            'title'      => _a('Credit'),
            'controller' => 'credit',
            'permission' => 'public',
            'block'      => 1,
        ],
    ],
];
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
        'order'    => [
            'title'  => _a('Orders'),
            'access' => [//'admin',
            ],
        ],
        'invoice'  => [
            'title'  => _a('Invoice'),
            'access' => [//'admin',
            ],
        ],
        'logs'     => [
            'title'  => _a('Logs'),
            'access' => [//'admin',
            ],
        ],
        'gateway'  => [
            'title'  => _a('Gateway'),
            'access' => [//'admin',
            ],
        ],
        'delivery' => [
            'title'  => _a('Delivery'),
            'access' => [//'admin',
            ],
        ],
        'location' => [
            'title'  => _a('Location'),
            'access' => [//'admin',
            ],
        ],
        'credit'   => [
            'title'  => _a('Credit'),
            'access' => [//'admin',
            ],
        ],
    ],
    // Front section
    'front' => [
        'public' => [
            'title'  => _a('Global public resource'),
            'access' => [
                'guest',
                'member',
            ],
        ],
    ],
];
<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * Module meta
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

return [
    'admin' => [
        'order'             => [
            'label'      => _a('Orders and invoices'),
            'route'      => 'admin',
            'controller' => 'order',
            'action'     => 'index',
            'pages'      => [
                'list'    => [
                    'label'      => _a('Order list'),
                    'route'      => 'admin',
                    'controller' => 'order',
                    'action'     => 'index',
                ],
                'invoice' => [
                    'label'      => _a('Invoice list'),
                    'route'      => 'admin',
                    'controller' => 'invoice',
                    'action'     => 'index',
                ],
                'logs'    => [
                    'label'      => _a('Logs'),
                    'route'      => 'admin',
                    'controller' => 'logs',
                    'action'     => 'index',
                ],
                'add'     => [
                    'label'      => _a('New order'),
                    'route'      => 'admin',
                    'controller' => 'order',
                    'action'     => 'add',
                ],
            ],
        ],
        'gateway'           => [
            'label'      => _a('Gateway'),
            'route'      => 'admin',
            'controller' => 'gateway',
            'action'     => 'index',
        ],
        'delivery-location' => [
            'label'      => _a('Delivery and location'),
            'route'      => 'admin',
            'controller' => 'delivery',
            'action'     => 'index',
            'pages'      => [
                'delivery'     => [
                    'label'      => _a('Delivery'),
                    'route'      => 'admin',
                    'controller' => 'delivery',
                    'action'     => 'index',
                ],
                'location'     => [
                    'label'      => _a('Location'),
                    'route'      => 'admin',
                    'controller' => 'location',
                    'action'     => 'index',
                ],
                'delivery-add' => [
                    'label'      => _a('New delivery'),
                    'route'      => 'admin',
                    'controller' => 'delivery',
                    'action'     => 'update',
                ],
                'location-add' => [
                    'label'      => _a('New location'),
                    'route'      => 'admin',
                    'controller' => 'location',
                    'action'     => 'update',
                ],
            ],
        ],
        'credit'            => [
            'label'      => _a('Credit'),
            'route'      => 'admin',
            'controller' => 'credit',
            'action'     => 'index',
            'pages'      => [
                'list'    => [
                    'label'      => _a('User list'),
                    'route'      => 'admin',
                    'controller' => 'credit',
                    'action'     => 'index',
                ],
                'history' => [
                    'label'      => _a('History'),
                    'route'      => 'admin',
                    'controller' => 'credit',
                    'action'     => 'history',
                ],
                'add'     => [
                    'label'      => _a('Add'),
                    'route'      => 'admin',
                    'controller' => 'credit',
                    'action'     => 'update',
                ],
            ],
        ],
        'promocode'         => [
            'label'      => _a('Promotional codes'),
            'route'      => 'admin',
            'controller' => 'promocode',
            'action'     => 'index',
            'pages'      => [
                'list'    => [
                    'label'      => _a('Promotional codes'),
                    'route'      => 'admin',
                    'controller' => 'promocode',
                    'action'     => 'index',
                ],
                'add'     => [
                    'label'      => _a('Add'),
                    'route'      => 'admin',
                    'controller' => 'promocode',
                    'action'     => 'manage',
                ],
            ],
        ],
    ],
];

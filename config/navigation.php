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
            'permission' => [
                'resource' => 'order',
            ],
            'route'      => 'admin',
            'controller' => 'order',
            'action'     => 'index',
            'pages'      => [
                'list'    => [
                    'label'      => _a('Order list'),
                    'permission' => [
                        'resource' => 'order',
                    ],
                    'route'      => 'admin',
                    'controller' => 'order',
                    'action'     => 'index',
                ],
                'invoice' => [
                    'label'      => _a('Invoice list'),
                    'permission' => [
                        'resource' => 'invoice',
                    ],
                    'route'      => 'admin',
                    'controller' => 'invoice',
                    'action'     => 'index',
                ],
                'logs'    => [
                    'label'      => _a('Logs'),
                    'permission' => [
                        'resource' => 'logs',
                    ],
                    'route'      => 'admin',
                    'controller' => 'logs',
                    'action'     => 'index',
                ],
                'add'     => [
                    'label'      => _a('New order'),
                    'permission' => [
                        'resource' => 'order',
                    ],
                    'route'      => 'admin',
                    'controller' => 'order',
                    'action'     => 'add',
                ],
            ],
        ],
        'report'           => [
            'label'      => _a('Report'),
            'route'      => 'admin',
            'controller' => 'report',
            'action'     => 'index',
        ],
        'gateway'           => [
            'label'      => _a('Gateway'),
            'permission' => [
                'resource' => 'gateway',
            ],
            'route'      => 'admin',
            'controller' => 'gateway',
            'action'     => 'index',
        ],
        'delivery-location' => [
            'label'      => _a('Delivery and location'),
            'permission' => [
                'resource' => 'delivery',
            ],
            'route'      => 'admin',
            'controller' => 'delivery',
            'action'     => 'index',
            'pages'      => [
                'delivery'     => [
                    'label'      => _a('Delivery'),
                    'permission' => [
                        'resource' => 'delivery',
                    ],
                    'route'      => 'admin',
                    'controller' => 'delivery',
                    'action'     => 'index',
                ],
                'location'     => [
                    'label'      => _a('Location'),
                    'permission' => [
                        'resource' => 'location',
                    ],
                    'route'      => 'admin',
                    'controller' => 'location',
                    'action'     => 'index',
                ],
                'delivery-add' => [
                    'label'      => _a('New delivery'),
                    'permission' => [
                        'resource' => 'delivery',
                    ],
                    'route'      => 'admin',
                    'controller' => 'delivery',
                    'action'     => 'update',
                ],
                'location-add' => [
                    'label'      => _a('New location'),
                    'permission' => [
                        'resource' => 'location',
                    ],
                    'route'      => 'admin',
                    'controller' => 'location',
                    'action'     => 'update',
                ],
            ],
        ],
        'credit'            => [
            'label'      => _a('Credit'),
            'permission' => [
                'resource' => 'credit',
            ],
            'route'      => 'admin',
            'controller' => 'credit',
            'action'     => 'index',
            'pages'      => [
                'list'    => [
                    'label'      => _a('User list'),
                    'permission' => [
                        'resource' => 'credit',
                    ],
                    'route'      => 'admin',
                    'controller' => 'credit',
                    'action'     => 'index',
                ],
                'history' => [
                    'label'      => _a('History'),
                    'permission' => [
                        'resource' => 'credit',
                    ],
                    'route'      => 'admin',
                    'controller' => 'credit',
                    'action'     => 'history',
                ],
                'add'     => [
                    'label'      => _a('Add'),
                    'permission' => [
                        'resource' => 'credit',
                    ],
                    'route'      => 'admin',
                    'controller' => 'credit',
                    'action'     => 'update',
                ],
            ],
        ],
        'promocode'         => [
            'label'      => _a('Promotional codes'),
            'permission' => [
                'resource' => 'promocode',
            ],
            'route'      => 'admin',
            'controller' => 'promocode',
            'action'     => 'index',
            'pages'      => [
                'list'    => [
                    'label'      => _a('Promotional codes'),
                    'permission' => [
                        'resource' => 'promocode',
                    ],
                    'route'      => 'admin',
                    'controller' => 'promocode',
                    'action'     => 'index',
                ],
                'add'     => [
                    'label'      => _a('Add'),
                    'permission' => [
                        'resource' => 'promocode',
                    ],
                    'route'      => 'admin',
                    'controller' => 'promocode',
                    'action'     => 'manage',
                ],
            ],
        ],
        'subscription'         => [
            'label'      => _a('Subscription'),
            'permission' => [
                'resource' => 'subscription',
            ],
            'route'      => 'admin',
            'controller' => 'subscription',
            'action'     => 'index',
            'pages'      => [
                'index'    => [
                    'label'      => _a('Subscription'),
                    'permission' => [
                        'resource' => 'subscription',
                    ],
                    'route'      => 'admin',
                    'controller' => 'subscription',
                    'action'     => 'index',
                ],
                'detail'    => [
                    'label'      => _a('Detail'),
                    'permission' => [
                        'resource' => 'subscription',
                    ],
                    'route'      => 'admin',
                    'controller' => 'subscription',
                    'action'     => 'detail',
                ],
                'customer'     => [
                    'label'      => _a('Customers'),
                    'permission' => [
                        'resource' => 'subscription',
                    ],
                    'route'      => 'admin',
                    'controller' => 'subscription',
                    'action'     => 'customer',
                ],
                'product'     => [
                    'label'      => _a('Products'),
                    'permission' => [
                        'resource' => 'subscription',
                    ],
                    'route'      => 'admin',
                    'controller' => 'subscription',
                    'action'     => 'product',
                ],
            ],
        ],
    ],
];

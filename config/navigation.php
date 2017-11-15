<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Module meta
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

return array(
    'admin' => array(
        'order' => array(
            'label' => _a('Orders and invoices'),
            'route' => 'admin',
            'controller' => 'order',
            'action' => 'index',
            'pages' => array(
                'list' => array(
                    'label' => _a('Order list'),
                    'route' => 'admin',
                    'controller' => 'order',
                    'action' => 'index',
                ),
                'invoice' => array(
                    'label' => _a('Invoice list'),
                    'route' => 'admin',
                    'controller' => 'invoice',
                    'action' => 'index',
                ),
                'logs' => array(
                    'label' => _a('Logs'),
                    'route' => 'admin',
                    'controller' => 'logs',
                    'action' => 'index',
                ),
                'add' => array(
                    'label' => _a('New order'),
                    'route' => 'admin',
                    'controller' => 'order',
                    'action' => 'add',
                ),
            ),
        ),
        'gateway' => array(
            'label' => _a('Gateway'),
            'route' => 'admin',
            'controller' => 'gateway',
            'action' => 'index',
        ),
        'delivery-location' => array(
            'label' => _a('Delivery and location'),
            'route' => 'admin',
            'controller' => 'delivery',
            'action' => 'index',
            'pages' => array(
                'delivery' => array(
                    'label' => _a('Delivery'),
                    'route' => 'admin',
                    'controller' => 'delivery',
                    'action' => 'index',
                ),
                'location' => array(
                    'label' => _a('Location'),
                    'route' => 'admin',
                    'controller' => 'location',
                    'action' => 'index',
                ),
                'delivery-add' => array(
                    'label' => _a('New delivery'),
                    'route' => 'admin',
                    'controller' => 'delivery',
                    'action' => 'update',
                ),
                'location-add' => array(
                    'label' => _a('New location'),
                    'route' => 'admin',
                    'controller' => 'location',
                    'action' => 'update',
                ),
            ),
        ),
        'credit' => array(
            'label' => _a('Credit'),
            'route' => 'admin',
            'controller' => 'credit',
            'action' => 'index',
            'pages' => array(
                'list' => array(
                    'label' => _a('User list'),
                    'route' => 'admin',
                    'controller' => 'credit',
                    'action' => 'index',
                ),
                'history' => array(
                    'label' => _a('History'),
                    'route' => 'admin',
                    'controller' => 'credit',
                    'action' => 'history',
                ),
                'add' => array(
                    'label' => _a('Add'),
                    'route' => 'admin',
                    'controller' => 'credit',
                    'action' => 'update',
                ),
            ),
        ),
        'promocode' => array(
            'label' => _a('Promotional codes'),
            'route' => 'admin',
            'controller' => 'promocode',
            'action' => 'index',
            'pages' => array(
                
            ),
        ),
    ),
);
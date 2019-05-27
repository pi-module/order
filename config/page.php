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
return array(
    // Admin section
    'admin' => array(
        array(
            'label' => _a('Orders'),
            'controller' => 'order',
            'permission' => 'order',
        ),
        array(
            'label' => _a('Invoice'),
            'controller' => 'invoice',
            'permission' => 'invoice',
        ),
        array(
            'label' => _a('Logs'),
            'controller' => 'logs',
            'permission' => 'logs',
        ),
        array(
            'label' => _a('Gateway'),
            'controller' => 'gateway',
            'permission' => 'gateway',
        ),
        array(
            'label' => _a('Delivery'),
            'controller' => 'delivery',
            'permission' => 'delivery',
        ),
        array(
            'label' => _a('Location'),
            'controller' => 'location',
            'permission' => 'location',
        ),
        array(
            'label' => _a('Credit'),
            'controller' => 'credit',
            'permission' => 'credit',
        ),
    ),
    // Front section
    'front' => array(
        array(
            'title' => _a('Index page'),
            'controller' => 'index',
            'permission' => 'public',
            'block' => 1,
        ),
        array(
            'title' => _a('Checkout'),
            'controller' => 'checkout',
            'permission' => 'public',
            'block' => 1,
        ),
        array(
            'title' => _a('Detail'),
            'controller' => 'detail',
            'permission' => 'public',
            'block' => 1,
        ),
        array(
            'title' => _a('Credit'),
            'controller' => 'credit',
            'permission' => 'public',
            'block' => 1,
        ),
    ),
);
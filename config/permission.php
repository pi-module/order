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
        'order' => array(
            'title' => _a('Orders'),
            'access' => array(//'admin',
            ),
        ),
        'invoice' => array(
            'title' => _a('Invoice'),
            'access' => array(//'admin',
            ),
        ),
        'logs' => array(
            'title' => _a('Logs'),
            'access' => array(//'admin',
            ),
        ),
        'gateway' => array(
            'title' => _a('Gateway'),
            'access' => array(//'admin',
            ),
        ),
        'delivery' => array(
            'title' => _a('Delivery'),
            'access' => array(//'admin',
            ),
        ),
        'location' => array(
            'title' => _a('Location'),
            'access' => array(//'admin',
            ),
        ),
        'credit' => array(
            'title' => _a('Credit'),
            'access' => array(//'admin',
            ),
        ),
    ),
    // Front section
    'front' => array(
        'public' => array(
            'title' => _a('Global public resource'),
            'access' => array(
                'guest',
                'member',
            ),
        ),
    ),
);
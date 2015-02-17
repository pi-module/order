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
return array(
    // route name
    'order'  => array(
        'name'      => 'order',
        'type'      => 'Module\Order\Route\Order',
        'options'   => array(
            'route'     => '/order',
            'defaults'  => array(
                'module'        => 'order',
                'controller'    => 'index',
                'action'        => 'index'
            )
        ),
    )
);
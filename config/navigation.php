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
        'invoice' => array(
            'label'       => _a('Invoice'),
            'route'       => 'admin',
            'controller'  => 'invoice',
            'action'      => 'index',
        ),
        'logs' => array(
            'label'       => _a('Logs'),
            'route'       => 'admin',
            'controller'  => 'logs',
            'action'      => 'index',
        ),
        'gateway' => array(
            'label'       => _a('Gateway'),
            'route'       => 'admin',
            'controller'  => 'gateway',
            'action'      => 'index',
        ),
    ),
);
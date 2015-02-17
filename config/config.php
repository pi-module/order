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
    'category' => array(
        array(
            'title'  => _a('Admin'),
            'name'   => 'admin'
        ),
        array(
            'title'  => _a('Payment'),
            'name'   => 'payment'
        ),
    ),
    'item' => array(
    	// Admin
        'admin_perpage' => array(
            'category'     => 'admin',
            'title'        => _a('Perpage'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'number_int',
            'value'        => 50
        ),
        // Payment
        'payment_gateway_error_url' => array(
            'category'     => 'payment',
            'title'        => _a('Payment gateway error url'),
            'description'  => _a('Set back url when gateway have error, set empty for default url'),
            'edit'         => 'text',
            'filter'       => 'string',
        ),
        'payment_shownotpay' => array(
            'category'     => 'payment',
            'title'        => _a('Show not pay payments'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'payment_anonymous' => array(
            'category'     => 'payment',
            'title'        => _a('Anonymous users can pay'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
        ),
    ),
);
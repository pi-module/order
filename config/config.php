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
            'title'  => _a('Order'),
            'name'   => 'order'
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
        // Order
        'order_method' => array(
            'title'        => _a('Order method'),
            'description'  => '',
            'edit'         => array(
                'type'     => 'select',
                'options'  => array(
                    'options' => array(
                        'both'      => _a('Both'),
                        'online'    => _a('Online'),
                        'offline'   => _a('Offline'),
                        'inactive'  => _a('Inactive'),
                    ),
                ),
            ),
            'filter'       => 'text',
            'value'        => 'both',
            'category'     => 'order',
        ),
        'order_anonymous' => array(
            'category'     => 'order',
            'title'        => _a('Anonymous users can pay'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
        ),
        'order_code_prefix' => array(
            'category'     => 'order',
            'title'        => _a('Code Prefix'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 'Pi' 
        ),
        'order_mail' => array(
            'category'     => 'order',
            'title'        => _a('List of mails for send order notification'),
            'description'  => _a('Use `|` as delimiter to separate mails'),
            'edit'         => 'textarea',
            'filter'       => 'string',
            'value'        => ''
        ),
        'order_name' => array(
            'category'     => 'order',
            'title'        => _a('Show name'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_email' => array(
            'category'     => 'order',
            'title'        => _a('Show email'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_phone' => array(
            'category'     => 'order',
            'title'        => _a('Show phone'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_mobile' => array(
            'category'     => 'order',
            'title'        => _a('Show mobile'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_company' => array(
            'category'     => 'order',
            'title'        => _a('Show company'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_address' => array(
            'category'     => 'order',
            'title'        => _a('Show address'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_country' => array(
            'category'     => 'order',
            'title'        => _a('Show country'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_state' => array(
            'category'     => 'order',
            'title'        => _a('Show state'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_city' => array(
            'category'     => 'order',
            'title'        => _a('Show city'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_zip' => array(
            'category'     => 'order',
            'title'        => _a('Show Zip code'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_packing' => array(
            'category'     => 'order',
            'title'        => _a('Show packing'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_location' => array(
            'category'     => 'order',
            'title'        => _a('Show location'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_delivery' => array(
            'category'     => 'order',
            'title'        => _a('Show delivery'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_gateway' => array(
            'category'     => 'order',
            'title'        => _a('Show gateway'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_promo' => array(
            'category'     => 'order',
            'title'        => _a('Show promo'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
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
    ),
);
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
            'title'  => _a('Price'),
            'name'   => 'price'
        ),
        array(
            'title'  => _a('Payment'),
            'name'   => 'payment'
        ),
        array(
            'title'  => _a('Sms'),
            'name'   => 'sms'
        ),
        array(
            'title'  => _a('Installment'),
            'name'   => 'installment'
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
        'order_idnumber' => array(
            'category'     => 'order',
            'title'        => _a('Show ID number'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
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
        // Price
        'price_custom' => array(
            'category'     => 'price',
            'title'        => _a('Use custom price'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
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
        // Sms
        'sms_order_user' => array(
            'category'     => 'sms',
            'title'        => _a('New order notification to user'),
            'description'  => _a('Dear %s %s, Your order added on system'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear %s %s, Your order added on system'),
        ),
        'sms_order_admin' => array(
            'category'     => 'sms',
            'title'        => _a('New order notification to admin'),
            'description'  => _a('Dear admin, New order added on system'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear admin, New order added on system'),
        ),
        'sms_invoice_user' => array(
            'category'     => 'sms',
            'title'        => _a('Pay invoice notification to user'),
            'description'  => _a('Dear %s %s, Your invoice paid successfully'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear %s %s, Your invoice paid successfully'),
        ),
        'sms_invoice_admin' => array(
            'category'     => 'sms',
            'title'        => _a('Pay invoice notification to admin'),
            'description'  => _a('Dear admin, Your customer paid invoice successfully'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear admin, Your customer paid invoice successfully'),
        ),
        // Installment
        'installment_credit' => array(
            'category'     => 'installment',
            'title'        => _a('Reduce from user credit'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_1_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 1 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('1 month'),
        ),
        'plan_1_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 1 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 50,
        ),
        'plan_1_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 1 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 0,
        ),
        'plan_1_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 1 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 1,
        ),
        'plan_2_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 2 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('2 months'),
        ),
        'plan_2_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 2 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_2_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 2 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 1.5,
        ),
        'plan_2_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 2 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 2,
        ),
        'plan_3_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 3 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('3 months'),
        ),
        'plan_3_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 3 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_3_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 3 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 2,
        ),
        'plan_3_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 3 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),

        'plan_4_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 4 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('6 months'),
        ),
        'plan_4_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 4 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_4_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 4 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 2.5,
        ),
        'plan_4_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 4 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 6,
        ),
        'plan_5_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 5 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('10 months'),
        ),
        'plan_5_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 5 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_5_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 5 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),
        'plan_5_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 5 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 10,
        ),
    ),
);
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
            'title'  => _a('Customize'),
            'name'   => 'customize'
        ),
        array(
            'title'  => _a('Payment'),
            'name'   => 'payment'
        ),
        array(
            'title'  => _a('Notification'),
            'name'   => 'notification'
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
        'order_testmode' => array(
            'category'     => 'order',
            'title'        => _a('Test mode'),
            'description'  => _a('By test mode , you can disable payment on bank and test order level. dont use it on active websites'),
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
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
            'title'        => _a('Order code Prefix'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 'Pi' 
        ),
        'invoice_code_prefix' => array(
            'category'     => 'order',
            'title'        => _a('Invoice code Prefix'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 'Pi' 
        ),
        'order_update_user' => array(
            'category'     => 'order',
            'title'        => _a('Update user profile'),
            'description'  => _a('Update user profile by order informations by user confirmation'),
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
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
        'order_company_extra' => array(
            'category'     => 'order',
            'title'        => _a('Show company extra'),
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
        'order_address2' => array(
            'category'     => 'order',
            'title'        => _a('Show address 2'),
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
        'order_countrylist' => array(
            'category'     => 'order',
            'title'        => _a('Country list'),
            'description'  => _a('Use `|` as delimiter to separate countres'),
            'edit'         => 'textarea',
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
        'order_location_delivery' => array(
            'category'     => 'order',
            'title'        => _a('Show location and delivery'),
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
        'order_usernote' => array(
            'category'     => 'order',
            'title'        => _a('Show user note'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'order_term' => array(
            'category'     => 'order',
            'title'        => _a('Show order term'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
        ),
        'order_termtext' => array(
            'category'     => 'order',
            'title'        => _a('Order term text'),
            'description'  => '',
            'edit'         => 'textarea',
        ),
        'order_termurl' => array(
            'category'     => 'order',
            'title'        => _a('Order term url'),
            'description'  => '',
            'edit'         => 'text',
        ),
        'order_additional_price_product' => array(
            'category'     => 'order',
            'title'        => _a('Product additional price'),
            'description'  => _a('Additional price as ready and delivery, just for products'),
            'edit'         => 'text',
            'filter'       => 'number_int',
            'value'        => 0
        ),
        'order_additional_price_service' => array(
            'category'     => 'order',
            'title'        => _a('Service additional price'),
            'description'  => _a('Additional price as setup, just for servicies'),
            'edit'         => 'text',
            'filter'       => 'number_int',
            'value'        => 0
        ),
        'order_payment' => array(
            'title'        => _a('After save order'),
            'description'  => '',
            'edit'         => array(
                'type'     => 'select',
                'options'  => array(
                    'options' => array(
                        'invoice' => _a('Go to invoice page'),
                        'payment' => _a('Go to payment page'),
                    ),
                ),
            ),
            'filter'       => 'text',
            'value'        => 'invoice',
            'category'     => 'order',
        ),
        'order_sellerlogo' => array(
            'category'     => 'order',
            'title'        => _a('Seller logo'),
            'description'  => _a('Put logo full URL'),
            'edit'         => 'text',
            'filter'       => 'string',
        ),
        'order_sellerinfo' => array(
            'category'     => 'order',
            'title'        => _a('Seller information'),
            'description'  => _a('HTML tags supported, use on invoice page'),
            'edit'         => 'textarea',
            'filter'       => 'string',
        ),
        'order_notification_email' => array(
            'category'     => 'order',
            'title'        => _a('Notification email for each module'),
            'description'  => _a('Setup format : shop,shop@mysite.com|guide,guide@mysite.com'),
            'edit'         => 'textarea',
            'filter'       => 'string',
        ),
        // Customize
        'price_custom' => array(
            'category'     => 'customize',
            'title'        => _a('Use custom price'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 0
        ),
        'date_format' => array(
            'category'     => 'customize',
            'title'        => _a('Date format'),
            'description'  => _a('For example : yyyy-MM-dd'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 'yyyy-MM-dd'
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
        'payment_image' => array(
            'category'     => 'payment',
            'title'        => _a('Website image URL'),
            'description'  => _a('Use on some payment gateway like paypal.'),
            'edit'         => 'text',
            'filter'       => 'string',
        ),
        // Notification
        'sms_order_user' => array(
            'category'     => 'notification',
            'title'        => _a('New order notification to user'),
            'description'  => _a('Dear %s %s, Your order added on system'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear %s %s, Your order added on system'),
        ),
        'sms_order_admin' => array(
            'category'     => 'notification',
            'title'        => _a('New order notification to admin'),
            'description'  => _a('Dear admin, New order added on system'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear admin, New order added on system'),
        ),
        'sms_invoice_user' => array(
            'category'     => 'notification',
            'title'        => _a('Pay invoice notification to user'),
            'description'  => _a('Dear %s %s, Your invoice paid successfully'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear %s %s, Your invoice paid successfully'),
        ),
        'sms_invoice_admin' => array(
            'category'     => 'notification',
            'title'        => _a('Pay invoice notification to admin'),
            'description'  => _a('Dear admin, Your customer paid invoice successfully'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear admin, Your customer paid invoice successfully'),
        ),
        'notification_cron_invoice' => array(
            'category'     => 'notification',
            'title'        => _a('Set X day before due date'),
            'description'  => _a('Notification invoice due date, X day before invoice time by cron'),
            'edit'         => 'text',
            'filter'       => 'number_int',
            'value'        => 2
        ),
        'sms_invoice_duedate' => array(
            'category'     => 'notification',
            'title'        => _a('Duedate invoice notification to user'),
            'description'  => _a('Dear %s %s, You have duedate invoice on next %s days'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear %s %s, You have duedate invoice on next %s days'),
        ),
        'notification_cron_expired' => array(
            'category'     => 'notification',
            'title'        => _a('Set X day after expired'),
            'description'  => _a('Notification invoice expired, X day after invoice time by cron'),
            'edit'         => 'text',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'user_expired_invoice' => array(
            'category'     => 'notification',
            'title'        => _a('Expired invoice notification to user'),
            'description'  => _a('Dear %s %s, You have expired invoice on %s days ago'),
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('Dear %s %s, You have expired invoice on %s days ago'),
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
        'plan_1_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 1'),
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
        'plan_2_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 2'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
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
        'plan_3_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 3'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
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
        'plan_4_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 4'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_4_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 4 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('4 months'),
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
            'value'        => 4,
        ),
        'plan_5_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 5'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_5_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 5 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('5 months'),
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
            'value'        => 5,
        ),
        'plan_6_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 6'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_6_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 6 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('6 months'),
        ),
        'plan_6_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 6 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_6_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 6 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),
        'plan_6_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 6 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 6,
        ),
        'plan_7_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 7'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_7_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 7 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('7 months'),
        ),
        'plan_7_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 7 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_7_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 7 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),
        'plan_7_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 7 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 7,
        ),
        'plan_8_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 8'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_8_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 8 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('8 months'),
        ),
        'plan_8_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 8 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_8_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 8 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),
        'plan_8_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 8 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 8,
        ),
        'plan_9_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 9'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_9_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 9 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('9 months'),
        ),
        'plan_9_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 9 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_9_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 9 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),
        'plan_9_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 9 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 9,
        ),
        'plan_10_show' => array(
            'category'     => 'installment',
            'title'        => _a('Show plan 10'),
            'description'  => '',
            'edit'         => 'checkbox',
            'filter'       => 'number_int',
            'value'        => 1
        ),
        'plan_10_title' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 10 : Title'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => _a('10 months'),
        ),
        'plan_10_prepayment' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 10 : Prepayment'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 20,
        ),
        'plan_10_profit' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 10 : Profit'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 3,
        ),
        'plan_10_total' => array(
            'category'     => 'installment',
            'title'        => _a('Plan 10 : Total'),
            'description'  => '',
            'edit'         => 'text',
            'filter'       => 'string',
            'value'        => 10,
        ),
    ),
);
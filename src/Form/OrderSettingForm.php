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

namespace Module\Order\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class OrderSettingForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderSettingFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // status_order
        $this->add(
            [
                'name'    => 'status_order',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Order'),
                    'value_options' => [
                        0 => __('All'),
                        1 => __('Not processed'),
                        2 => __('Orders validated'),
                        3 => __('Orders pending'),
                        4 => __('Orders failed'),
                        5 => __('Orders cancelled'),
                        6 => __('Fraudulent orders'),
                        7 => __('Orders finished'),
                    ],
                ],
            ]
        );
        // status_payment
        $this->add(
            [
                'name'    => 'status_payment',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Payment'),
                    'value_options' => [
                        0 => __('All'),
                        1 => __('UnPaid'),
                        2 => __('Paid'),
                    ],
                ],
            ]
        );
        // status_delivery
        $this->add(
            [
                'name'    => 'status_delivery',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Delivery'),
                    'value_options' => [
                        0 => __('All'),
                        1 => __('Not processed'),
                        2 => __('Packed'),
                        3 => __('Posted'),
                        4 => __('Delivered'),
                        5 => __('Back eaten'),
                    ],
                ],
            ]
        );
        // can_pay
        $this->add(
            [
                'name'    => 'can_pay',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Can pay?'),
                    'value_options' => [
                        0 => __('All'),
                        1 => __('Yes'),
                        2 => __('No'),
                    ],
                ],
            ]
        );
        // type_payment
        $this->add(
            [
                'name'    => 'type_payment',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Type payment'),
                    'value_options' => [
                        'all'         => __('All'),
                        'free'        => __('Free'),
                        'onetime'     => __('Onetime'),
                        'recurring'   => __('Recurring'),
                        'installment' => __('Installment'),
                    ],
                ],
            ]
        );
        // type_commodity
        $this->add(
            [
                'name'    => 'type_commodity',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Type commodity'),
                    'value_options' => [
                        'all'     => __('All'),
                        'product' => __('Product'),
                        'service' => __('Service'),
                        'booking' => __('Booking'),
                    ],
                ],
            ]
        );
        // code
        $this->add(
            [
                'name'       => 'code',
                'options'    => [
                    'label' => __('Code'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // mobile
        $this->add(
            [
                'name'       => 'mobile',
                'options'    => [
                    'label' => __('Mobile'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // email
        $this->add(
            [
                'name'       => 'email',
                'options'    => [
                    'label' => __('Email'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // city
        $this->add(
            [
                'name'       => 'city',
                'options'    => [
                    'label' => __('City'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // uid
        $this->add(
            [
                'name'       => 'uid',
                'options'    => [
                    'label' => __('User ID'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // id_number
        $this->add(
            [
                'name'       => 'id_number',
                'options'    => [
                    'label' => __('ID number'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // first_name
        $this->add(
            [
                'name'       => 'first_name',
                'options'    => [
                    'label' => __('First name'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // last_name
        $this->add(
            [
                'name'       => 'last_name',
                'options'    => [
                    'label' => __('Last name'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // zip_code
        $this->add(
            [
                'name'       => 'zip_code',
                'options'    => [
                    'label' => __('Zip code'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // company
        $this->add(
            [
                'name'       => 'company',
                'options'    => [
                    'label' => __('Company'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // Save
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Filter'),
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
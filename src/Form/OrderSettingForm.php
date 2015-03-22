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

namespace Module\Order\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class OrderSettingForm  extends BaseForm
{
    public function __construct($name = null, $option = array())
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
        $this->add(array(
            'name' => 'status_order',
            'type' => 'select',
            'options' => array(
                'label' => __('Order'),
                'value_options'  => array(
                    0 => __('All'),
                    1 => __('Not processed'),
                    2 => __('Orders validated'),
                    3 => __('Orders pending'),
                    4 => __('Orders failed'),
                    5 => __('Orders cancelled'),
                    6 => __('Fraudulent orders'),
                    7 => __('Orders finished'),
                ),
            ),
        ));
        // status_payment
        $this->add(array(
            'name' => 'status_payment',
            'type' => 'select',
            'options' => array(
                'label' => __('Payment'),
                'value_options' => array(
                    0 => __('All'),
                    1 => __('UnPaid'),
                    2 => __('Paid'),
                ),
            ),
        ));
        // status_delivery
        $this->add(array(
            'name' => 'status_delivery',
            'type' => 'select',
            'options' => array(
                'label' => __('Delivery'),
                'value_options' => array(
                    0 => __('All'),
                    1 => __('Not processed'),
                    2 => __('Packed'),
                    3 => __('Posted'),
                    4 => __('Delivered'),
                    5 => __('Back eaten'),
                ),
            ),
        ));
        // code
        $this->add(array(
            'name'             => 'code',
            'options'          => array(
                'label'        => __('Code'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // mobile
        $this->add(array(
            'name'             => 'mobile',
            'options'          => array(
                'label'        => __('Mobile'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // email
        $this->add(array(
            'name'             => 'email',
            'options'          => array(
                'label'        => __('Email'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // city
        $this->add(array(
            'name'             => 'city',
            'options'          => array(
                'label'        => __('City'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // uid
        $this->add(array(
            'name'             => 'uid',
            'options'          => array(
                'label'        => __('User ID'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // id_number
        $this->add(array(
            'name'             => 'id_number',
            'options'          => array(
                'label'        => __('ID number'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // first_name
        $this->add(array(
            'name'             => 'first_name',
            'options'          => array(
                'label'        => __('First name'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // last_name
        $this->add(array(
            'name'             => 'last_name',
            'options'          => array(
                'label'        => __('Last name'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // zip_code
        $this->add(array(
            'name'             => 'zip_code',
            'options'          => array(
                'label'        => __('Zip code'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // company
        $this->add(array(
            'name'             => 'company',
            'options'          => array(
                'label'        => __('Company'),
            ),
            'attributes'       => array(
                'type'         => 'text',
                'description'  => '',
            )
        ));
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Filter'),
                'class' => 'btn btn-primary',
            )
        ));
    }
}
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
                'value_options' => array(
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
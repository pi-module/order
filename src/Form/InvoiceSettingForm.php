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

class InvoiceSettingForm extends BaseForm
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
        // orderid
        $this->add(array(
            'name' => 'orderid',
            'options' => array(
                'label' => __('Order ID'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // uid
        $this->add(array(
            'name' => 'uid',
            'options' => array(
                'label' => __('User ID'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // payment_status
        $this->add(array(
            'name' => 'payment_status',
            'type' => 'select',
            'options' => array(
                'label' => __('Payment status'),
                'value_options' => array(
                    '' => __('All'),
                    1 => __('Paid'),
                    2 => __('UnPaid'),
                    'delayed' => __('Delayed'),
                ),
            ),
        ));
        // start
        $this->add(array(
            'name' => 'start',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Start from'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'id' => 'time-start',
            )
        ));
        // end
        $this->add(array(
            'name' => 'end',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('End to'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'id' => 'time-end',
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
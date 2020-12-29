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

class InvoiceSettingForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new InvoiceSettingFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // orderid
        $this->add(
            [
                'name'       => 'orderid',
                'options'    => [
                    'label' => __('Order ID'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // randomid
        $this->add(
            [
                'name'       => 'randomid',
                'options'    => [
                    'label' => __('Bank ID'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // payment_status
        $this->add(
            [
                'name'    => 'payment_status',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Payment status'),
                    'value_options' => [
                        ''        => __('All'),
                        1         => __('Paid'),
                        2         => __('UnPaid'),
                        'delayed' => __('Delayed'),
                    ],
                ],
            ]
        );
        // start
        $this->add(
            [
                'name'       => 'start',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Start from'),
                    'datepicker' => [
                        'format' => 'yyyy-mm-dd',
                    ],
                ],
                'attributes' => [
                    'id' => 'time-start',
                ],
            ]
        );
        // end
        $this->add(
            [
                'name'       => 'end',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('End to'),
                    'datepicker' => [
                        'format' => 'yyyy-mm-dd',
                    ],
                ],
                'attributes' => [
                    'id' => 'time-end',
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

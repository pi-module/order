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

class UpdateOrderForm extends BaseForm
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new UpdateOrderFilter;
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
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Update'),
                'class' => 'btn btn-primary',
            )
        ));
    }
}
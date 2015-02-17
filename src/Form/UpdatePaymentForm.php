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

namespace Module\Shop\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class UpdatePaymentForm  extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new UpdatePaymentFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // status_payment
        $this->add(array(
            'name' => 'status_payment',
            'type' => 'select',
            'options' => array(
                'label' => __('Payment'),
                'value_options' => array(
                    1 => __('UnPaid'),
                    2 => __('Paid'),
                ),
            ),
        ));
        // payment_adapter
        $this->add(array(
            'name' => 'payment_adapter',
            'type' => 'Module\Shop\Form\Element\Gateway',
            'options' => array(
                'label' => __('Adapter'),
                'payment_adapter' => '',
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
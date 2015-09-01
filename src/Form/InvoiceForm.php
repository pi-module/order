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

class InvoiceForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new InvoiceFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // product_price
        $this->add(array(
            'name' => 'product_price',
            'options' => array(
                'label' => __('Product price'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // shipping_price
        $this->add(array(
            'name' => 'shipping_price',
            'options' => array(
                'label' => __('Shipping price'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // packing_price
        $this->add(array(
            'name' => 'packing_price',
            'options' => array(
                'label' => __('Packing price'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // vat_price
        $this->add(array(
            'name' => 'vat_price',
            'options' => array(
                'label' => __('Vat price'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // time_duedate
        $this->add(array(
            'name' => 'time_duedate',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Due date'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'id' => 'time-start',
                'required' => true,
            )
        ));
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Save'),
                'class' => 'btn btn-primary',
            )
        ));
    }
}
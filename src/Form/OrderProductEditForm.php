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

class OrderProductEditForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderProductEditFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // id
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'required' => true,
            ),
        ));
        // order
        $this->add(array(
            'name' => 'order',
            'attributes' => array(
                'type' => 'hidden',
                'required' => true,
            ),
        ));
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
        // setup_price
        $this->add(array(
            'name' => 'setup_price',
            'options' => array(
                'label' => __('Setup price'),
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
        // number
        $this->add(array(
            'name' => 'number',
            'type' => 'select',
            'options' => array(
                'label' => __('Number'),
                'value_options' => array(
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                    6 => 6,
                    7 => 7,
                    8 => 8,
                    9 => 9,
                    10 => 10,
                    11 => 11,
                    12 => 12,
                    13 => 13,
                    14 => 14,
                    15 => 15,
                ),
            ),
        ));
        // invoice
        $this->add(array(
            'name' => 'invoice',
            'options' => array(
                'label' => __('Select invoice to edit fee'),
                'value_options' => $this->option['invoice'],
            ),
            'type' => 'radio',
            'attributes' => array(
                'required' => true,
            ),
        ));
        // Save order
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Edit'),
                'class' => 'btn btn-success',
            )
        ));
    }
}
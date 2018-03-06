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

class OrderProductForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderProductFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        
        $this->add(array(
            'name' => 'module',
            'options' => array(
                'label' => __('Module name'),
                'value_options' => array('order' => 'order', 'shop' => 'shop', 'guide' => 'guide'),
            ),
            'type' => 'select',
            'attributes' => array(
                'required' => true,
            ),
        ));
        
         
        $this->add(array(
            'name' => 'product_type',
            'options' => array(
                'label' => __('Product type'),
            ),
            'attributes' => array(
                'type' => 'text',
            )
        ));
        
       $this->add(array(
            'name' => 'product',
            'options' => array(
                'label' => __('Product / service ID'),
            ),
            'attributes' => array(
                'type' => 'text',
                'required' => true,
            )
        ));
        
        // module_item
        $this->add(array(
            'name' => 'module_item',
            'options' => array(
                'label' => __('Module item associated to the product'),
            ),
            'attributes' => array(
                'type' => 'text',
            )
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
        
        $this->add(array(
            'name' => 'time_start',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Time start'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'required' => false,
            )
        ));
        
        $this->add(array(
            'name' => 'time_end',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Time end'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'required' => false,
            )
        ));
        
        // Save order
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Save'),
                'class' => 'btn btn-success',
            )
        ));
    }
}
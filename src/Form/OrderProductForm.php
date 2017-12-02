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
        // id
        $this->add(array(
            'name' => 'id',
            'options' => array(
                'label' => __('Product / service ID'),
            ),
            'attributes' => array(
                'type' => 'text',
                'required' => true,
            )
        ));
        // invoice
        $this->add(array(
            'name' => 'invoice',
            'options' => array(
                'label' => __('Select invoice to add fee'),
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
                'value' => __('Save'),
                'class' => 'btn btn-success',
            )
        ));
    }
}
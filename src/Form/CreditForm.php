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

class CreditForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new CreditFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // uid
        $this->add(array(
            'name' => 'uid',
            'options' => array(
                'label' => __('User ID'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
                'required' => true,
            )
        ));
        // amount
        $this->add(array(
            'name' => 'amount',
            'options' => array(
                'label' => __('Amount'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
                'required' => true,
            )
        ));
        // status_fluctuation
        $this->add(array(
            'name' => 'status_fluctuation',
            'type' => 'select',
            'options' => array(
                'label' => __('Type commodity'),
                'value_options' => array(
                    'increase' => __('Increase'),
                    'decrease' => __('Decrease'),
                ),
            ),
            'attributes' => array(
                'required' => true,
            )
        ));
        // message_user
        $this->add(array(
            'name' => 'message_user',
            'options' => array(
                'label' => __('User message'),
            ),
            'attributes' => array(
                'type' => 'textarea',
                'rows' => '5',
                'cols' => '40',
                'required' => true,
            )
        ));
        // message_admin
        $this->add(array(
            'name' => 'message_admin',
            'options' => array(
                'label' => __('Admin message'),
            ),
            'attributes' => array(
                'type' => 'textarea',
                'rows' => '5',
                'cols' => '40',
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
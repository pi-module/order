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

class CreditSettingForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new CreditSettingFilter($this->option);
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
            )
        ));
        // first_name
        $this->add(array(
            'name' => 'first_name',
            'options' => array(
                'label' => __('First name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // last_name
        $this->add(array(
            'name' => 'last_name',
            'options' => array(
                'label' => __('Last name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // email
        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => __('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        // company
        $this->add(array(
            'name' => 'company',
            'options' => array(
                'label' => __('Company'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
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
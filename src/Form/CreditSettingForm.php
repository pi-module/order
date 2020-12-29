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
    public function __construct($name = null, $option = [])
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
        $this->add(
            [
                'name'       => 'uid',
                'options'    => [
                    'label' => __('User ID'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // first_name
        $this->add(
            [
                'name'       => 'first_name',
                'options'    => [
                    'label' => __('First name'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // last_name
        $this->add(
            [
                'name'       => 'last_name',
                'options'    => [
                    'label' => __('Last name'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // email
        $this->add(
            [
                'name'       => 'email',
                'options'    => [
                    'label' => __('Email'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // company
        $this->add(
            [
                'name'       => 'company',
                'options'    => [
                    'label' => __('Company'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
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

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

class PromocodeForm extends BaseForm
{
    protected $_id;

    public function __construct($id = null, $modules = [], $option = [])
    {
        $this->option   = $option;
        $this->_id      = $id;
        $this->_modules = $modules;
        parent::__construct();
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PromocodeFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(
            [
                'name'       => 'id',
                'attributes' => [
                    'type'  => 'hidden',
                    'value' => $this->_id,
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'code',
                'options'    => [
                    'label' => __('Code'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'promo',
                'options'    => [
                    'label' => __('Promo (%)'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );

        // time_start
        $this->add(
            [
                'name'       => 'datetimepicker_start',
                'type'       => 'text',
                'options'    => [
                    'label' => __('Start from'),

                ],
                'attributes' => [
                    'class'       => 'datetimepicker-input',
                    'required'    => true,
                    'id'          => 'datetimepicker_start',
                    'data-toggle' => "datetimepicker",
                    'data-target' => "#datetimepicker_start",
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'time_start',
                'type'       => 'hidden',
                'attributes' => [
                    'required' => true,
                ],
            ]
        );
        $this->add(
            [
                'name'       => 'datetimepicker_end',
                'type'       => 'text',
                'options'    => [
                    'label' => __('End to'),

                ],
                'attributes' => [
                    'class'       => 'datetimepicker-input',
                    'required'    => true,
                    'id'          => 'datetimepicker_end',
                    'data-toggle' => "datetimepicker",
                    'data-target' => "#datetimepicker_end",
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'time_end',
                'type'       => 'hidden',
                'attributes' => [
                    'required' => true,
                ],
            ]
        );


        $this->add(
            [
                'name'       => 'module',
                'type'       => 'select',
                'options'    => [
                    'label'         => __('Module'),
                    'value_options' => $this->_modules,
                ],
                'attributes' => [
                    'size'     => 5,
                    'multiple' => 1,
                ],
            ]
        );
        $this->add(
            [
                'name'    => 'showcode',
                'type'    => 'checkbox',
                'options' => [
                    'label' => __('Show Promo Code on front CTA'),
                ],

            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => $this->_id ? __('Edit') : __('Add'),
                    'class' => 'btn btn-success',
                ],
            ]
        );
    }
}

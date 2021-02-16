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

class LocationForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->delivery = $option['delivery'];
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $option             = [];
            $option['delivery'] = $this->delivery;
            $this->filter       = new LocationFilter($option);
        }
        return $this->filter;
    }

    public function init()
    {
        // title
        $this->add(
            [
                'name'       => 'title',
                'options'    => [
                    'label' => __('Title'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',

                ],
            ]
        );
        // parent
        $this->add(
            [
                'name'    => 'parent',
                'type'    => 'Module\Order\Form\Element\Location',
                'options' => [
                    'label'  => __('Parent'),
                    'parent' => 1,
                ],
            ]
        );
        // status
        $this->add(
            [
                'name'    => 'status',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Status'),
                    'value_options' => [
                        1 => __('Published'),
                        2 => __('Pending review'),
                        3 => __('Draft'),
                        4 => __('Private'),
                    ],
                ],
            ]
        );
        // delivery
        foreach ($this->delivery as $delivery) {
            // delivery fieldset
            $this->add(
                [
                    'name'    => sprintf('delivery_fieldset_%s', $delivery['id']),
                    'type'    => 'fieldset',
                    'options' => [
                        'label' => $delivery['title'],
                    ],
                ]
            );
            // active
            $this->add(
                [
                    'name'       => sprintf('delivery_active_%s', $delivery['id']),
                    'type'       => 'checkbox',
                    'options'    => [
                        'label' => sprintf(__('Is active %s ?'), $delivery['title']),
                    ],
                    'attributes' => [
                        'description' => '',
                    ],
                ]
            );
            // price
            $this->add(
                [
                    'name'       => sprintf('delivery_price_%s', $delivery['id']),
                    'options'    => [
                        'label' => __('Price'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
            // delivery_time
            $this->add(
                [
                    'name'       => sprintf('delivery_time_%s', $delivery['id']),
                    'options'    => [
                        'label' => __('Time ( days )'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
        }
        // Save
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Submit'),
                ],
            ]
        );
    }
}

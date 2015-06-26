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

class LocationForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->delivery = $option['delivery'];
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $option = array();
            $option['delivery'] = $this->delivery;
            $this->filter = new LocationFilter($option);
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
            ),
        ));
        // title
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => __('Title'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',

            )
        ));
        // parent
        $this->add(array(
            'name' => 'parent',
            'type' => 'Module\Order\Form\Element\Location',
            'options' => array(
                'label' => __('Parent'),
                'parent' => 1,
            ),
        ));
        // status
        $this->add(array(
            'name' => 'status',
            'type' => 'select',
            'options' => array(
                'label' => __('Status'),
                'value_options' => array(
                    1 => __('Published'),
                    2 => __('Pending review'),
                    3 => __('Draft'),
                    4 => __('Private'),
                ),
            ),
        ));
        // delivery
        foreach ($this->delivery as $delivery) {
            // delivery fieldset
            $this->add(array(
                'name' => sprintf('delivery_fieldset_%s', $delivery['id']),
                'type' => 'fieldset',
                'options' => array(
                    'label' => $delivery['title'],
                ),
            ));
            // active
            $this->add(array(
                'name' => sprintf('delivery_active_%s', $delivery['id']),
                'type' => 'checkbox',
                'options' => array(
                    'label' => sprintf(__('Is active %s ?'), $delivery['title']),
                ),
                'attributes' => array(
                    'description' => '',
                )
            ));
            // price
            $this->add(array(
                'name' => sprintf('delivery_price_%s', $delivery['id']),
                'options' => array(
                    'label' => __('Price'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            // delivery_time
            $this->add(array(
                'name' => sprintf('delivery_time_%s', $delivery['id']),
                'options' => array(
                    'label' => __('Time ( days )'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
        }
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            )
        ));
    }
}
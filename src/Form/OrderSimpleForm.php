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

class OrderSimpleForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        $this->config = Pi::service('registry')->config->read('order', 'order');
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderSimpleFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(array(
            'name' => 'address_delivery_id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->option['delivery_address']
            ),
        ));
        $this->add(array(
            'name' => 'address_invoicing_id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->option['invoicing_address']
            ),
        ));
        // id_number
        /* if ($this->config['order_idnumber']) {
            $this->add(array(
                'name' => 'id_number',
                'options' => array(
                    'label' => __('ID number'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
        } */
        // packing
        if ($this->config['order_packing']) {
            $this->add(array(
                'name' => 'packing',
                'type' => 'checkbox',
                'options' => array(
                    'label' => __('Packing'),
                ),
                'attributes' => array(
                    'description' => '',
                )
            ));
        }
        // Check type_commodity
        $gatewayList = Pi::api('gateway', 'order')->getActiveGatewayName();
        switch ($this->option['type_commodity']) {
            case 'product':
                if ($this->config['order_location_delivery']) {
                    // location
                    $this->add(array(
                        'name' => 'location',
                        'options' => array(
                            'label' => __('Location'),
                        ),
                        'attributes' => array(
                            'type' => 'hidden',
                            'required' => true,
                        )
                    ));
                    // delivery
                    $this->add(array(
                        'name' => 'delivery',
                        'type' => 'Module\Order\Form\Element\Delivery',
                        'options' => array(
                            'label' => __('Delivery methods'),
                            'value_options' => array(),
                            'location' => $this->option['location'],
                        ),
                        'attributes' => array(
                            'id' => 'address-select-delivery',
                            'size' => 3,
                            'required' => true,
                        )
                    ));
                    // check gateway
                    if (count($gatewayList) == 1) {
                        $gatewayList = array_keys($gatewayList);
                        // gateway
                        $this->add(array(
                            'name' => 'gateway',
                            'attributes' => array(
                                'id' => 'address-select-payment',
                                'type' => 'hidden',
                                'value' => $gatewayList['0'],
                            ),
                        ));
                    } else {
                        // gateway
                        $this->add(array(
                            'name' => 'gateway',
                            'type' => 'radio',
                            'options' => array(
                                'label' => __('Adapter'),
                                'value_options' => array(),
                            ),
                            'attributes' => array(
                                'id' => 'address-select-payment',
                                'required' => true,
                            )
                        ));
                    }
                } else {
                    if (count($gatewayList) == 1) {
                        $gatewayList = array_keys($gatewayList);
                        // gateway
                        $this->add(array(
                            'name' => 'gateway',
                            'attributes' => array(
                                'id' => 'address-select-payment',
                                'type' => 'hidden',
                                'value' => $gatewayList['0'],
                            ),
                        ));
                    } else {
                        // gateway
                        $this->add(array(
                            'name' => 'gateway',
                            'type' => 'radio',
                            'options' => array(
                                'label' => __('Adapter'),
                                'value_options' => $gatewayList,
                            ),
                            'attributes' => array(
                                'id' => 'address-select-payment',
                                'required' => true,
                            )
                        ));
                    }
                }
                break;

            case 'service':
                if (count($gatewayList) == 1) {
                    $gatewayList = array_keys($gatewayList);
                    // gateway
                    $this->add(array(
                        'name' => 'gateway',
                        'attributes' => array(
                            'type' => 'hidden',
                            'value' => $gatewayList['0'],
                        ),
                    ));
                } else {
                    // gateway
                    $this->add(array(
                        'name' => 'gateway',
                        'type' => 'radio',
                        'options' => array(
                            'label' => __('Adapter'),
                            'value_options' => $gatewayList,
                        ),
                        'attributes' => array(
                            'id' => 'address-select-payment',
                            'required' => true,
                        )
                    ));
                }
                break;
        }
        // user_note
        if ($this->config['order_usernote']) {
            $this->add(array(
                'name' => 'user_note',
                'options' => array(
                    'label' => __('User note'),
                ),
                'attributes' => array(
                    'type' => 'textarea',
                    'rows' => '5',
                    'cols' => '40',
                    'placeholder' => __('Give here more details you think we need to know about'),
                )
            ));
        }
        // order_term
        if ($this->config['order_term'] && !empty($this->config['order_termurl'])) {
            $term = sprintf('<a href="%s" target="_blank">%s</a>', $this->config['order_termurl'], __('Terms & Conditions'));
            $term = sprintf(__('Accept the %s'), $term);
            $this->add(array(
                'name' => 'order_term',
                'type' => 'checkbox',
                'options' => array(
                    'label' => '',
                ),
                'attributes' => array(
                    'description' => $term,
                    'required' => true,
                )
            ));
        }
        // Save
        if ($this->config['order_payment'] == 'payment') {
            $title = __('Pay');
        } else {
            $title = __('Save order');
        }
        $class = 'btn btn-success submit_order_simple';
        if (!$this->option['delivery_address'] && !$this->option['invoicing_address']) {
            $class .= ' fortooltip disabled';
        }
        
        $this->add(array(
            'name' => 'submit_order_simple',
            'type' => 'submit',
            'attributes' => array(
                'value' => $title,
                'class' => $class,
                'title' => __('You need to create an address before pay')
            )
        ));
    }
}
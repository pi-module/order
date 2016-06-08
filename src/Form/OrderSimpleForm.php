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
        // customer_id
        $this->add(array(
            'name' => 'customer_id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        // name
        if ($this->config['order_name']) {
            // first_name
            $this->add(array(
                'name' => 'first_name',
                'options' => array(
                    'label' => __('First name'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
            // last_name
            $this->add(array(
                'name' => 'last_name',
                'options' => array(
                    'label' => __('Last name'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
        }
        // id_number
        if ($this->config['order_idnumber']) {
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
        }
        // email
        if ($this->config['order_email']) {
            $this->add(array(
                'name' => 'email',
                'options' => array(
                    'label' => __('Email'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
        }
        // phone
        if ($this->config['order_phone']) {
            $this->add(array(
                'name' => 'phone',
                'options' => array(
                    'label' => __('Phone'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // mobile
        if ($this->config['order_mobile']) {
            $this->add(array(
                'name' => 'mobile',
                'options' => array(
                    'label' => __('Mobile'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
        }
        // company
        if ($this->config['order_company']) {
            // company
            $this->add(array(
                'name' => 'company',
                'options' => array(
                    'label' => __('Company'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // company extra
        if ($this->config['order_company_extra']) {
            // company_id
            $this->add(array(
                'name' => 'company_id',
                'options' => array(
                    'label' => __('Company id'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
            // company_vat
            $this->add(array(
                'name' => 'company_vat',
                'options' => array(
                    'label' => __('Company vat'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // address
        if ($this->config['order_address']) {
            $this->add(array(
                'name' => 'address1',
                'options' => array(
                    'label' => __('Delivery address'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
        }
        // address 2
        if ($this->config['order_address2']) {
            $this->add(array(
                'name' => 'address2',
                'options' => array(
                    'label' => __('Invoicing Address'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // country
        if ($this->config['order_country']) {
            $this->add(array(
                'name' => 'country',
                'options' => array(
                    'label' => __('Country'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // state
        if ($this->config['order_state']) {
            $this->add(array(
                'name' => 'state',
                'options' => array(
                    'label' => __('State'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // city
        if ($this->config['order_city']) {
            $this->add(array(
                'name' => 'city',
                'options' => array(
                    'label' => __('City'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                )
            ));
        }
        // zip_code
        if ($this->config['order_zip']) {
            $this->add(array(
                'name' => 'zip_code',
                'options' => array(
                    'label' => __('Zip code'),
                ),
                'attributes' => array(
                    'type' => 'hidden',
                    'required' => true,
                )
            ));
        }
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
                            'id' => 'customer-select-delivery',
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
                                'id' => 'customer-select-payment',
                                'type' => 'hidden',
                                'value' => $gatewayList['0'],
                            ),
                        ));
                    } else {
                        // gateway
                        $this->add(array(
                            'name' => 'gateway',
                            'type' => 'select',
                            'options' => array(
                                'label' => __('Adapter'),
                                'value_options' => array(),
                            ),
                            'attributes' => array(
                                'id' => 'customer-select-payment',
                                'size' => 3,
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
                                'id' => 'customer-select-payment',
                                'type' => 'hidden',
                                'value' => $gatewayList['0'],
                            ),
                        ));
                    } else {
                        // gateway
                        $this->add(array(
                            'name' => 'gateway',
                            'type' => 'select',
                            'options' => array(
                                'label' => __('Adapter'),
                                'value_options' => $gatewayList,
                            ),
                            'attributes' => array(
                                'id' => 'customer-select-payment',
                                'size' => 1,
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
                        'type' => 'select',
                        'options' => array(
                            'label' => __('Adapter'),
                            'value_options' => $gatewayList,
                        ),
                        'attributes' => array(
                            'id' => 'customer-select-payment',
                            'size' => 1,
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
                    'description' => __('Give here more details you think we need to know about'),
                )
            ));
        }
        // promo_value
        if ($this->config['order_promo']) {
            $this->add(array(
                'name' => 'promo_value',
                'options' => array(
                    'label' => __('Promo'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
        }
        // order_term
        if ($this->config['order_term'] && !empty($this->config['order_termurl'])) {
            $term = sprintf('<a href="%s" target="_blank">%s</a>', $this->config['order_termurl'], __('Terms & Conditions'));
            $term = sprintf(__('Accept %s'), $term);
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
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => $title,
                'class' => 'btn btn-success',
            )
        ));
    }
}
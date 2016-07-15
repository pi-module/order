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

class OrderForm extends BaseForm
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
            $this->filter = new OrderFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // Check for load register form
        $registerFieldsName = array();
        if (Pi::service('module')->isActive('user')
            && !Pi::service('authentication')->hasIdentity()
            && isset($_SESSION['session_order'])
            && !empty($_SESSION['session_order'])
        ) {
            $registerFields = Pi::api('form', 'user')->loadFields('register');
            foreach ($registerFields as $field) {
                $registerFieldsName[] = $field['name'];
                if ($field['name'] != 'captcha') {
                    $this->add($field);
                }
            }
        }
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
            if (!in_array('first_name', $registerFieldsName)) {
                $this->add(array(
                    'name' => 'first_name',
                    'options' => array(
                        'label' => __('First name'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                        'required' => true,
                    )
                ));
            }
            // last_name
            if (!in_array('last_name', $registerFieldsName)) {
                $this->add(array(
                    'name' => 'last_name',
                    'options' => array(
                        'label' => __('Last name'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                        'required' => true,
                    )
                ));
            }
        }
        // id_number
        if ($this->config['order_idnumber'] && !in_array('id_number', $registerFieldsName)) {
            $this->add(array(
                'name' => 'id_number',
                'options' => array(
                    'label' => __('ID number'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // email
        if ($this->config['order_email'] && !in_array('email', $registerFieldsName)) {
            $this->add(array(
                'name' => 'email',
                'options' => array(
                    'label' => __('Email'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // phone
        if ($this->config['order_phone'] && !in_array('phone', $registerFieldsName)) {
            $this->add(array(
                'name' => 'phone',
                'options' => array(
                    'label' => __('Phone'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
        }
        // mobile
        if ($this->config['order_mobile'] && !in_array('mobile', $registerFieldsName)) {
            $this->add(array(
                'name' => 'mobile',
                'options' => array(
                    'label' => __('Mobile'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // company
        if ($this->config['order_company'] && !in_array('company', $registerFieldsName)) {
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
        }
        // company extra
        if ($this->config['order_company_extra']) {
            // company_id
            if (!in_array('company_id', $registerFieldsName)) {
                $this->add(array(
                    'name' => 'company_id',
                    'options' => array(
                        'label' => __('Company id'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                    )
                ));
            }
            // company_vat
            if (!in_array('company_vat', $registerFieldsName)) {
                $this->add(array(
                    'name' => 'company_vat',
                    'options' => array(
                        'label' => __('Company vat'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                    )
                ));
            }
        }
        // address
        if ($this->config['order_address1'] && !in_array('address1', $registerFieldsName)) {
            $this->add(array(
                'name' => 'address1',
                'options' => array(
                    'label' => __('Delivery address'),
                ),
                'attributes' => array(
                    'type' => 'textarea',
                    'rows' => '3',
                    'cols' => '40',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // address 2
        if ($this->config['order_address2'] && !in_array('address2', $registerFieldsName)) {
            $this->add(array(
                'name' => 'address2',
                'options' => array(
                    'label' => __('Invoicing Address'),
                ),
                'attributes' => array(
                    'type' => 'textarea',
                    'rows' => '3',
                    'cols' => '40',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // zip_code
        if ($this->config['order_zip'] && !in_array('zip_code', $registerFieldsName)) {
            $this->add(array(
                'name' => 'zip_code',
                'options' => array(
                    'label' => __('Zip code'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // city
        if ($this->config['order_city'] && !in_array('city', $registerFieldsName)) {
            $this->add(array(
                'name' => 'city',
                'options' => array(
                    'label' => __('City'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,

                )
            ));
        }
        // state
        if ($this->config['order_state'] && !in_array('state', $registerFieldsName)) {
            $this->add(array(
                'name' => 'state',
                'options' => array(
                    'label' => __('State'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
        }
        // country
        if ($this->config['order_country'] && !in_array('country', $registerFieldsName)) {
            if (!empty($this->config['order_countrylist'])) {
                // Set list
                $countries = explode('|', $this->config['order_countrylist']);
                foreach ($countries as $country) {
                    $countryList[$country] = $country;
                }
                // Make form
                $this->add(array(
                    'name' => 'country',
                    'type' => 'select',
                    'options' => array(
                        'label' => __('Country'),
                        'value_options' => $countryList,
                        'required' => true,
                    ),
                ));
            } else {
                $this->add(array(
                    'name' => 'country',
                    'options' => array(
                        'label' => __('Country'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                        'required' => true,
                    )
                ));
            }
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
                        'type' => 'Module\Order\Form\Element\Location',
                        'options' => array(
                            'label' => __('Location'),
                            'parent' => 1,
                        ),
                        'attributes' => array(
                            'id' => 'select-location',
                            'required' => true,
                        )
                    ));
                    // delivery
                    $this->add(array(
                        'name' => 'delivery',
                        'type' => 'select',
                        'options' => array(
                            'label' => __('Delivery methods'),
                            'value_options' => array(),
                        ),
                        'attributes' => array(
                            'id' => 'select-delivery',
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
                                'id' => 'select-payment',
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
                                'id' => 'select-payment',
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
                                'id' => 'select-payment',
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
                                'id' => 'select-payment',
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
                            'id' => 'select-payment',
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
        // Update profile confirmation
        if ($this->config['order_update_user'] && empty($registerFieldsName)) {
            $this->add(array(
                'name' => 'update_user',
                'type' => 'checkbox',
                'options' => array(
                    'label' => __('Update your profile by this information?'),
                ),
                'attributes' => array(
                    'description' => '',
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
            $term = sprintf(__('I accept %s'), $term);
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
        if (Pi::service('module')->isActive('user')
            && !Pi::service('authentication')->hasIdentity()
            && isset($_SESSION['session_order'])
            && !empty($_SESSION['session_order'])
        ) {
            if ($this->config['order_payment'] == 'payment') {
                $title = __('Register and pay');
            } else {
                $title = __('Register and save order');
            }
        } else {
            if ($this->config['order_payment'] == 'payment') {
                $title = __('Pay');
            } else {
                $title = __('Save order');
            }
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
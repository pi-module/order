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

class OrderForm extends BaseForm
{
    public function __construct($name = null, $option = [])
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
        $registerFieldsName = [];
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
        $this->add(
            [
                'name'       => 'address_id',
                'attributes' => [
                    'type' => 'hidden',
                ],
            ]
        );
        // name
        if ($this->config['order_name']) {
            // first_name
            if (!in_array('first_name', $registerFieldsName)) {
                $this->add(
                    [
                        'name'       => 'first_name',
                        'options'    => [
                            'label' => __('First name'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
            }
            // last_name
            if (!in_array('last_name', $registerFieldsName)) {
                $this->add(
                    [
                        'name'       => 'last_name',
                        'options'    => [
                            'label' => __('Last name'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
            }
        }
        // id_number
        if ($this->config['order_idnumber'] && !in_array('id_number', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'id_number',
                    'options'    => [
                        'label' => __('ID number'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // email
        if ($this->config['order_email'] && !in_array('email', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'email',
                    'options'    => [
                        'label' => __('Email'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // phone
        if ($this->config['order_phone'] && !in_array('phone', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'phone',
                    'options'    => [
                        'label' => __('Phone'),
                    ],
                    'attributes' => [
                        'type'        => 'tel',
                        'pattern'     => "^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$",
                        'description' => '', 'description' => '',

                    ],
                ]
            );
        }
        // mobile
        if ($this->config['order_mobile'] && !in_array('mobile', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'mobile',
                    'options'    => [
                        'label' => __('Mobile'),
                    ],
                    'attributes' => [
                        'type'        => 'tel',
                        'pattern'     => "^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$",
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // company
        if ($this->config['order_company'] && !in_array('company', $registerFieldsName)) {
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
        }
        // company extra
        if ($this->config['order_company_extra']) {
            // company_id
            if (!in_array('company_id', $registerFieldsName)) {
                $this->add(
                    [
                        'name'       => 'company_id',
                        'options'    => [
                            'label' => __('Company id'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                        ],
                    ]
                );
            }
            // company_vat
            if (!in_array('company_vat', $registerFieldsName)) {
                $this->add(
                    [
                        'name'       => 'company_vat',
                        'options'    => [
                            'label' => __('Company vat'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                        ],
                    ]
                );
            }
        }
        // address
        if ($this->config['order_address1'] && !in_array('address1', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'address1',
                    'options'    => [
                        'label' => __('Delivery address'),
                    ],
                    'attributes' => [
                        'type'        => 'textarea',
                        'rows'        => '3',
                        'cols'        => '40',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // address 2
        if ($this->config['order_address2'] && !in_array('address2', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'address2',
                    'options'    => [
                        'label' => __('Invoicing Address'),
                    ],
                    'attributes' => [
                        'type'        => 'textarea',
                        'rows'        => '3',
                        'cols'        => '40',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // zip_code
        if ($this->config['order_zip'] && !in_array('zip_code', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'zip_code',
                    'options'    => [
                        'label' => __('Zip code'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // city
        if ($this->config['order_city'] && !in_array('city', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'city',
                    'options'    => [
                        'label' => __('City'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,

                    ],
                ]
            );
        }
        // state
        if ($this->config['order_state'] && !in_array('state', $registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'state',
                    'options'    => [
                        'label' => __('State'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
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
                $this->add(
                    [
                        'name'    => 'country',
                        'type'    => 'select',
                        'options' => [
                            'label'         => __('Country'),
                            'value_options' => $countryList,
                            'required'      => true,
                        ],
                    ]
                );
            } else {
                $this->add(
                    [
                        'name'       => 'country',
                        'options'    => [
                            'label' => __('Country'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
            }
        }
        // packing
        if ($this->config['order_packing']) {
            $this->add(
                [
                    'name'       => 'packing',
                    'type'       => 'checkbox',
                    'options'    => [
                        'label' => __('Packing'),
                    ],
                    'attributes' => [
                        'description' => '',
                    ],
                ]
            );
        }
        // Check type_commodity
        $gatewayList = Pi::api('gateway', 'order')->getActiveGatewayName();
        switch ($this->option['type_commodity']) {
            case 'product':
                if ($this->config['order_location_delivery']) {
                    // location
                    $this->add(
                        [
                            'name'       => 'location',
                            'type'       => 'Module\Order\Form\Element\Location',
                            'options'    => [
                                'label'  => __('Location'),
                                'parent' => 1,
                            ],
                            'attributes' => [
                                'id'       => 'select-location',
                                'required' => true,
                            ],
                        ]
                    );
                    // delivery
                    $this->add(
                        [
                            'name'       => 'delivery',
                            'type'       => 'select',
                            'options'    => [
                                'label'         => __('Delivery methods'),
                                'value_options' => [],
                            ],
                            'attributes' => [
                                'id'       => 'select-delivery',
                                'size'     => 3,
                                'required' => true,
                            ],
                        ]
                    );
                    // check gateway
                    if (count($gatewayList) == 1) {
                        $gatewayList = array_keys($gatewayList);
                        // gateway
                        $this->add(
                            [
                                'name'       => 'gateway',
                                'attributes' => [
                                    'id'    => 'select-payment',
                                    'type'  => 'hidden',
                                    'value' => $gatewayList['0'],
                                ],
                            ]
                        );
                    } else {
                        // gateway
                        $this->add(
                            [
                                'name'       => 'gateway',
                                'type'       => 'select',
                                'options'    => [
                                    'label'         => __('Choose your payment method'),
                                    'value_options' => [],
                                ],
                                'attributes' => [
                                    'id'       => 'select-payment',
                                    'size'     => 3,
                                    'required' => true,
                                ],
                            ]
                        );
                    }
                } else {
                    if (count($gatewayList) == 1) {
                        $gatewayList = array_keys($gatewayList);
                        // gateway
                        $this->add(
                            [
                                'name'       => 'gateway',
                                'attributes' => [
                                    'id'    => 'select-payment',
                                    'type'  => 'hidden',
                                    'value' => $gatewayList['0'],
                                ],
                            ]
                        );
                    } else {
                        // gateway
                        $this->add(
                            [
                                'name'       => 'gateway',
                                'type'       => 'select',
                                'options'    => [
                                    'label'         => __('Choose your payment method'),
                                    'value_options' => $gatewayList,
                                ],
                                'attributes' => [
                                    'id'       => 'select-payment',
                                    'size'     => 1,
                                    'required' => true,
                                ],
                            ]
                        );
                    }
                }
                break;
            case 'booking':
            case 'service':
                if (count($gatewayList) == 1) {
                    $gatewayList = array_keys($gatewayList);
                    // gateway
                    $this->add(
                        [
                            'name'       => 'gateway',
                            'attributes' => [
                                'type'  => 'hidden',
                                'value' => $gatewayList['0'],
                            ],
                        ]
                    );
                } else {
                    // gateway
                    $this->add(
                        [
                            'name'       => 'gateway',
                            'type'       => 'select',
                            'options'    => [
                                'label'         => __('Choose your payment method'),
                                'value_options' => $gatewayList,
                            ],
                            'attributes' => [
                                'id'       => 'select-payment',
                                'size'     => 1,
                                'required' => true,
                            ],
                        ]
                    );
                }
                break;
        }
        // user_note
        if ($this->config['order_usernote']) {
            $this->add(
                [
                    'name'       => 'user_note',
                    'options'    => [
                        'label' => __('User note'),
                    ],
                    'attributes' => [
                        'type'        => 'textarea',
                        'rows'        => '5',
                        'cols'        => '40',
                        'description' => __('Give here more details you think we need to know about'),
                    ],
                ]
            );
        }
        // Update profile confirmation
        if ($this->config['order_update_user'] && empty($registerFieldsName)) {
            $this->add(
                [
                    'name'       => 'update_user',
                    'type'       => 'checkbox',
                    'options'    => [
                        'label' => __('Update your profile by this information?'),
                    ],
                    'attributes' => [
                        'description' => '',
                    ],
                ]
            );
        }
        // order_term
        if ($this->config['order_term'] && !empty($this->config['order_termurl'])) {
            $term = sprintf('<a href="%s" target="_blank">%s</a>', $this->config['order_termurl'], __('Terms & Conditions'));
            $term = sprintf(__('I accept %s'), $term);
            $this->add(
                [
                    'name'       => 'order_term',
                    'type'       => 'checkbox',
                    'options'    => [
                        'label' => '',
                    ],
                    'attributes' => [
                        'description' => $term,
                        'required'    => true,
                    ],
                ]
            );
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
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => $title,
                    'class' => 'btn btn-success',
                ],
            ]
        );
    }
}
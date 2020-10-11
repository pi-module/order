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

class AddressForm extends BaseForm
{
    protected $_id;

    public function __construct($name = null, $option = [], $id = null)
    {
        $this->option = $option;
        $this->config = Pi::service('registry')->config->read('order');
        $this->_id    = $id;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new AddressFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // Set group
        $groups = [];

        // Set general group
        if ($this->config['address_type'] == 'both') {
            $groups['general'] = [
                'label'    => __('Account Type'),
                'class'    => 'address-general',
                'elements' => ['account_type'],

            ];
        }

        // Set company group
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            $groups['company'] = [
                'label'    => __('Company Information'),
                'class'    => 'address-company',
                'elements' => [
                    'company',
                    'company_address1',
                    'company_address2',
                    'company_zip_code',
                    'company_city',
                    'company_country',
                    'company_id',
                    'company_vat',
                ],
            ];
        }

        // Set individual group
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            $groups['individual'] = [
                'label'    => __('Legal representative (Personal Information)'),
                'class'    => 'address-individual',
                'elements' => [
                    'first_name',
                    'last_name',
                    'id_number',
                    'birthday',
                    'email',
                    'mobile',
                    'address1',
                    'address2',
                    'country',
                    'state',
                    'city',
                    'zip_code',
                ],
            ];
        }

        // account_type
        if ($this->config['address_type'] == 'both') {
            $this->add(
                [
                    'name'       => 'account_type',
                    'type'       => 'radio',
                    'options'    => [
                        'label'         => __('Account type'),
                        'value_options' => [
                            'individual' => __('Individual'),
                            'company'    => __('Company'),
                        ],
                    ],
                    'attributes' => [
                        'required'     => true,
                        'value'        => $this->config['address_type_default'],
                        'autocomplete' => 'user-password',
                    ],
                ]
            );
        } else {
            $this->add(
                [
                    'name'       => 'account_type',
                    'attributes' => [
                        'type' => 'hidden',
                        'value' => $this->config['address_type']
                    ],
                ]
            );
        }

        //address_id
        $this->add(
            [
                'name'       => 'address_id',
                'attributes' => [
                    'type' => 'hidden',
                ],
            ]
        );


        // company
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_company']) {
                $this->add(
                    [
                        'name'       => 'company',
                        'options'    => [
                            'label' => __('Company'),
                        ],
                        'attributes' => [
                            'class'        => 'complementary',
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('company', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // company_address1
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_address1']) {
                $this->add(
                    [
                        'name'       => 'company_address1',
                        'options'    => [
                            'label' => __('Address'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('company_address1', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // company_address2
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_address2']) {
                $this->add(
                    [
                        'name'       => 'company_address2',
                        'options'    => [
                            'label' => __('Address addition'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => false,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('company_address2', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // company_zip_code
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_zip']) {
                $this->add(
                    [
                        'name'       => 'company_zip_code',
                        'options'    => [
                            'label' => __('Zip code'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('company_zip_code', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // company_city
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_city']) {
                $this->add(
                    [
                        'name'       => 'company_city',
                        'options'    => [
                            'label' => __('City'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',


                        ],
                    ]
                );
            } else {
                if (($key = array_search('company_city', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // order_state
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_state']) {
                $this->add(
                    [
                        'name'       => 'company_state',
                        'options'    => [
                            'label' => __('State'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('company_state', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // country
        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_country']) {
                if (!empty($this->config['order_countrylist'])) {
                    // Set list
                    $countries = explode('|', $this->config['order_countrylist']);
                    foreach ($countries as $country) {
                        $countryList[$country] = $country;
                    }
                    // Make form
                    $this->add(
                        [
                            'name'    => 'company_country',
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
                            'name'       => 'company_country',
                            'options'    => [
                                'label' => __('Country'),
                            ],
                            'attributes' => [
                                'type'         => 'text',
                                'description'  => '',
                                'required'     => true,
                                'autocomplete' => 'user-password',

                            ],
                        ]
                    );
                }
            } else {
                if (($key = array_search('company_country', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        if (in_array($this->config['address_type'], ['both', 'company'])) {
            if ($this->config['order_address2'] || $this->config['order_company_extra']) {
                // company extra
                if ($this->config['order_company_extra']) {
                    // company_id
                    $this->add(
                        [
                            'name'       => 'company_id',
                            'options'    => [
                                'label' => __('Company ID'),
                            ],
                            'attributes' => [
                                'class'        => 'complementary',
                                'type'         => 'text',
                                'description'  => '',
                                'required'     => true,
                                'autocomplete' => 'user-password',

                            ],
                        ]
                    );
                    // company_vat
                    $this->add(
                        [
                            'name'       => 'company_vat',
                            'options'    => [
                                'label' => __('Company VAT'),
                            ],
                            'attributes' => [
                                'class'        => 'complementary',
                                'type'         => 'text',
                                'description'  => '',
                                'required'     => true,
                                'autocomplete' => 'user-password',

                            ],
                        ]
                    );
                } else {
                    if (($key = array_search('company_id', $groups['company']['elements'])) !== false) {
                        unset($groups['company']['elements'][$key]);
                    }
                    if (($key = array_search('company_vat', $groups['company']['elements'])) !== false) {
                        unset($groups['company']['elements'][$key]);
                    }
                }
            } else {
                if (($key = array_search('company_id', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
                if (($key = array_search('company_vat', $groups['company']['elements'])) !== false) {
                    unset($groups['company']['elements'][$key]);
                }
            }
        }

        // Set html row 1
        $this->add(
            [
                'name'       => 'html-raw',
                'attributes' => [
                    'value' => '<hr class="html-raw" />',
                    'type'  => 'html-raw',
                ],
            ]
        );

        // Set html row 2
        $this->add(
            [
                'name'       => 'html-raw2',
                'attributes' => [
                    'value' => sprintf('<p class="html-raw">%s</p>', __('Please fill in the following information')),
                    'type'  => 'html-raw',
                    'class' => 'html-raw',
                ],
            ]
        );


        // name
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_name']) {
                // first_name
                $this->add(
                    [
                        'name'       => 'first_name',
                        'options'    => [
                            'label' => __('First name'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

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
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );

            } else {
                if (($key = array_search('first_name', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
                if (($key = array_search('last_name', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // birthday
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_birthday']) {
                $this->add(
                    [
                        'name'       => 'birthday',
                        'type'       => 'datepicker',
                        'options'    => [
                            'label'      => __('Birthday'),
                            'datepicker' => [
                                'format'         => 'dd/mm/yyyy',
                                'autoclose'      => true,
                                'todayBtn'       => true,
                                'todayHighlight' => true,
                                'weekStart'      => 1,
                                'endDate'        => '-18y',
                                'orientation'    => 'bottom',
                            ],
                        ],
                        'attributes' => [
                            'required'     => true,
                            'autocomplete' => false,
                        ],
                    ]
                );
            } else {
                if (($key = array_search('birthday', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // id_number
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_idnumber']) {
                $this->add(
                    [
                        'name'       => 'id_number',
                        'options'    => [
                            'label' => __('ID number'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('id_number', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // email
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_email']) {
                $this->add(
                    [
                        'name'       => 'email',
                        'options'    => [
                            'label' => __('Email'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('email', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // phone
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_phone']) {
                $this->add(
                    [
                        'name'       => 'phone',
                        'options'    => [
                            'label' => __('Phone'),
                        ],
                        'attributes' => [
                            'type'         => 'tel',
                            'pattern'      => Pi::api('api', 'order')->patternPhone(),
                            'description'  => Pi::service('i18n')->getLocale() == 'fa' ? '' : __('International number expected (+33123456789)'),
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('phone', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // mobile
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_mobile']) {
                $this->add(
                    [
                        'name'       => 'mobile',
                        'options'    => [
                            'label' => __('Mobile'),
                        ],
                        'attributes' => [
                            'type'         => 'tel',
                            'title'        => __("example : +33123456789"),
                            'pattern'      => Pi::api('api', 'order')->patternPhone(),
                            'description'  => Pi::service('i18n')->getLocale() == 'fa' ? '' : __('International number expected (+33123456789)'),
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('mobile', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // address
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_address1']) {
                $this->add(
                    [
                        'name'       => 'address1',
                        'options'    => [
                            'label' => __('Address'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('address1', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // address 2
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_address2']) {
                $this->add(
                    [
                        'name'       => 'address2',
                        'options'    => [
                            'label' => __('Address addition'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => false,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('address2', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // zip_code
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_zip']) {
                $this->add(
                    [
                        'name'       => 'zip_code',
                        'options'    => [
                            'label' => __('Zip code'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('zip_code', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // city
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_city']) {
                $this->add(
                    [
                        'name'       => 'city',
                        'options'    => [
                            'label' => __('City'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',


                        ],
                    ]
                );
            } else {
                if (($key = array_search('city', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // state
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_state']) {
                $this->add(
                    [
                        'name'       => 'state',
                        'options'    => [
                            'label' => __('State'),
                        ],
                        'attributes' => [
                            'type'         => 'text',
                            'description'  => '',
                            'required'     => true,
                            'autocomplete' => 'user-password',

                        ],
                    ]
                );
            } else {
                if (($key = array_search('state', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // country
        if (in_array($this->config['address_type'], ['both', 'individual'])) {
            if ($this->config['order_country']) {
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
                                'type'         => 'text',
                                'description'  => '',
                                'required'     => true,
                                'autocomplete' => 'user-password',

                            ],
                        ]
                    );
                }
            } else {
                if (($key = array_search('country', $groups['individual']['elements'])) !== false) {
                    unset($groups['individual']['elements'][$key]);
                }
            }
        }

        // submit
        $this->add(
            [
                'name'       => 'submit_address',
                'type'       => 'submit',
                'attributes' => [
                    'value' => $this->_id ? __('Save') : __('Add'),
                    'class' => 'btn btn-success',
                ],
            ]
        );

        // Set group
        $this->setGroups($groups);
    }
}

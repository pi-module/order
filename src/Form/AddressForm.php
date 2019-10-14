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

    public function __construct($name = null, $id = null, $option = [])
    {
        $this->option = $option;
        $this->config = Pi::service('registry')->config->read('order', 'order');
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


        $this->add(array(
            'name' => 'account_type',
            'type'       => 'radio',
            'options' => array(
                'label' => __('Account type'),
                'value_options' => array(
                    'individual' => __('Individual'),
                    'company' => __('Company'),
                ),
            ),
            'attributes' => array(
                'required' => true,
                'value' => 0,
            )
        ));

        $this->add(
            [
                'name'       => 'address_id',
                'attributes' => [
                    'type' => 'hidden',
                ],
            ]
        );


        // company
        if ($this->config['order_company']) {
            // company
            $this->add(
                [
                    'name' => 'company',
                    'options' => [
                        'label' => __('Company'),
                    ],
                    'attributes' => [
                        'class' => 'complementary',
                        'type' => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }

        if ($this->config['order_address1']) {
            $this->add(
                [
                    'name'       => 'company_address1',
                    'options'    => [
                        'label' => __('Address'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }

        // address 2
        if ($this->config['order_address2']) {
            $this->add(
                [
                    'name'       => 'company_address2',
                    'options'    => [
                        'label' => __('Address addition'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => false,
                    ],
                ]
            );
        }

        // zip_code
        if ($this->config['order_zip']) {
            $this->add(
                [
                    'name'       => 'company_zip_code',
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
        if ($this->config['order_city']) {
            $this->add(
                [
                    'name'       => 'company_city',
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
        if ($this->config['order_state']) {
            $this->add(
                [
                    'name'       => 'company_state',
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
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
            }
        }

        if ($this->config['order_address2'] || $this->config['order_company_extra']) {

            // company extra
            if ($this->config['order_company_extra']) {
                // company_id
                $this->add(
                    [
                        'name'       => 'company_id',
                        'options'    => [
                            'label' => __('Company id'),
                        ],
                        'attributes' => [
                            'class'       => 'complementary',
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
                // company_vat
                $this->add(
                    [
                        'name'       => 'company_vat',
                        'options'    => [
                            'label' => __('Company vat'),
                        ],
                        'attributes' => [
                            'class'       => 'complementary',
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
            }
        }

        $this->add(
            [
                'name'       => 'html-raw',
                'attributes' => [
                    'value' => '<hr class="html-raw" />',
                    'type'  => 'html-raw',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'html-raw2',
                'attributes' => [
                    'value' => '<p class="html-raw">Merci de renseigner les informations suivantes</p>',
                    'type'  => 'html-raw',
                    'class' => 'html-raw'
                ],
            ]
        );





        // name
        if ($this->config['order_name']) {
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
                        'required'    => true,
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
                        'required'    => true,
                    ],
                ]
            );

        }

        // time_start
        $this->add(array(
            'name' => 'birthday',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Birthday'),
                'datepicker' => array(
                    'format' => 'dd/mm/yyyy',
                    'autoclose' => true,
                    'todayBtn' => true,
                    'todayHighlight' => true,
                    'weekStart' => 1,
                    'endDate' => '-18y',
                    'orientation' => 'bottom'
                ),
            ),
            'attributes' => array(
                'required' => true,
                'autocomplete' => false
            )
        ));

        // id_number
        if ($this->config['order_idnumber']) {
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
        if ($this->config['order_email']) {
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
        if ($this->config['order_phone']) {
            $this->add(
                [
                    'name'       => 'phone',
                    'options'    => [
                        'label' => __('Phone'),
                    ],
                    'attributes' => [
                        'type'        => 'tel',
                        'pattern'     => Pi::api('api', 'order')->patternPhone(),
                        'description' => Pi::service('i18n')->getLocale() == 'fa' ? '' : __('International number expected (+33123456789)'),

                    ],
                ]
            );
        }
        // mobile
        if ($this->config['order_mobile']) {
            $this->add(
                [
                    'name'       => 'mobile',
                    'options'    => [
                        'label' => __('Mobile'),
                    ],
                    'attributes' => [
                        'type'        => 'tel',
                        'title'       => __("example : +33123456789"),
                        'pattern'     => Pi::api('api', 'order')->patternPhone(),
                        'description' => Pi::service('i18n')->getLocale() == 'fa' ? '' : __('International number expected (+33123456789)'),
                        'required'    => true,
                    ],
                ]
            );
        }

        // address
        if ($this->config['order_address1']) {
            $this->add(
                [
                    'name'       => 'address1',
                    'options'    => [
                        'label' => __('Address'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }

        // address 2
        if ($this->config['order_address2']) {
            $this->add(
                [
                    'name'       => 'address2',
                    'options'    => [
                        'label' => __('Address addition'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => false,
                    ],
                ]
            );
        }

        // zip_code
        if ($this->config['order_zip']) {
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
        if ($this->config['order_city']) {
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
        if ($this->config['order_state']) {
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
                            'type'        => 'text',
                            'description' => '',
                            'required'    => true,
                        ],
                    ]
                );
            }
        }



        $this->add(
            [
                'name'       => 'submit_address',
                'type'       => 'submit',
                'attributes' => [
                    'value' => $this->_id ? __('Edit') : __('Add'),
                    'class' => 'btn btn-success',
                ],
            ]
        );
    }
}

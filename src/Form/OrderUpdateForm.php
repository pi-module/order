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

class OrderUpdateForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderUpdateFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // uid
        if ($this->option['mode'] == 'add') {
            $this->add(
                [
                    'name'       => 'uid',
                    'options'    => [
                        'label' => __('User ID'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }

        $this->add(
            [
                'name'       => 'time_order',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Order date'),
                    'datepicker' => [
                        'format'         => 'yyyy-mm-dd',
                        'autoclose'      => true,
                        'todayBtn'       => true,
                        'todayHighlight' => true,
                        'weekStart'      => 1,
                    ],
                ],
                'attributes' => [
                    'required' => true,
                ],
            ]
        );
        $gatewayList = Pi::api('gateway', 'order')->getAdminGatewayList();
        $this->add(
            [
                'name'       => 'default_gateway',
                'type'       => 'select',
                'options'    => [
                    'label'         => __('Choose your payment method'),
                    'value_options' => $gatewayList,
                ],
                'attributes' => [
                    'id'       => 'select-payment',
                    'required' => true,
                ],
            ]
        );

        // name
        if ($this->option['config']['order_name']) {
            // first_name
            $this->add(
                [
                    'name'       => 'delivery_first_name',
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
                    'name'       => 'delivery_last_name',
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

            $this->add(
                [
                    'name'       => 'invoicing_first_name',
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
                    'name'       => 'invoicing_last_name',
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
        // id_number
        if ($this->option['config']['order_idnumber']) {
            $this->add(
                [
                    'name'       => 'delivery_id_number',
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
            $this->add(
                [
                    'name'       => 'invoicing_id_number',
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
        if ($this->option['config']['order_email']) {
            $this->add(
                [
                    'name'       => 'delivery_email',
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
            $this->add(
                [
                    'name'       => 'invoicing_email',
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
        if ($this->option['config']['order_phone']) {
            $this->add(
                [
                    'name'       => 'delivery_phone',
                    'options'    => [
                        'label' => __('Phone'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',

                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_phone',
                    'options'    => [
                        'label' => __('Phone'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',

                    ],
                ]
            );
        }
        // mobile
        if ($this->option['config']['order_mobile']) {
            $this->add(
                [
                    'name'       => 'delivery_mobile',
                    'options'    => [
                        'label' => __('Mobile'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_mobile',
                    'options'    => [
                        'label' => __('Mobile'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                        'required'    => true,
                    ],
                ]
            );
        }
        // company
        if ($this->option['config']['order_company']) {
            // company
            $this->add(
                [
                    'name'       => 'delivery_company',
                    'options'    => [
                        'label' => __('Company'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_company',
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
        if ($this->option['config']['order_company_extra']) {
            // company_id
            $this->add(
                [
                    'name'       => 'delivery_company_id',
                    'options'    => [
                        'label' => __('Company ID'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_company_id',
                    'options'    => [
                        'label' => __('Company ID'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
            // company_vat
            $this->add(
                [
                    'name'       => 'delivery_company_vat',
                    'options'    => [
                        'label' => __('Company VAT'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_company_vat',
                    'options'    => [
                        'label' => __('Company VAT'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
        }
        // address
        if ($this->option['config']['order_address1']) {
            $this->add(
                [
                    'name'       => 'delivery_address1',
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
            $this->add(
                [
                    'name'       => 'invoicing_address1',
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
        if ($this->option['config']['order_address2']) {
            $this->add(
                [
                    'name'       => 'delivery_address2',
                    'options'    => [
                        'label' => __('Address addition'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_address2',
                    'options'    => [
                        'label' => __('Address addition'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',
                    ],
                ]
            );
        }
        // country
        if ($this->option['config']['order_country']) {
            if (!empty($this->option['config']['order_countrylist'])) {
                // Set list
                $countries = explode('|', $this->option['config']['order_countrylist']);
                foreach ($countries as $country) {
                    $countryList[$country] = $country;
                }
                // Make form
                $this->add(
                    [
                        'name'    => 'delivery_country',
                        'type'    => 'select',
                        'options' => [
                            'label'         => __('Country'),
                            'value_options' => $countryList,
                        ],
                    ]
                );
                $this->add(
                    [
                        'name'    => 'invoicing_country',
                        'type'    => 'select',
                        'options' => [
                            'label'         => __('Country'),
                            'value_options' => $countryList,
                        ],
                    ]
                );
            } else {
                $this->add(
                    [
                        'name'       => 'delivery_country',
                        'options'    => [
                            'label' => __('Country'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                        ],
                    ]
                );
                $this->add(
                    [
                        'name'       => 'invoicing_country',
                        'options'    => [
                            'label' => __('Country'),
                        ],
                        'attributes' => [
                            'type'        => 'text',
                            'description' => '',
                        ],
                    ]
                );
            }
        }
        // state
        if ($this->option['config']['order_state']) {
            $this->add(
                [
                    'name'       => 'delivery_state',
                    'options'    => [
                        'label' => __('State'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',

                    ],
                ]
            );
            $this->add(
                [
                    'name'       => 'invoicing_state',
                    'options'    => [
                        'label' => __('State'),
                    ],
                    'attributes' => [
                        'type'        => 'text',
                        'description' => '',

                    ],
                ]
            );
        }
        // city
        if ($this->option['config']['order_city']) {
            $this->add(
                [
                    'name'       => 'delivery_city',
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
            $this->add(
                [
                    'name'       => 'invoicing_city',
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
        // zip_code
        if ($this->option['config']['order_zip']) {
            $this->add(
                [
                    'name'       => 'delivery_zip_code',
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

            $this->add(
                [
                    'name'       => 'invoicing_zip_code',
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
        // packing
        if ($this->option['config']['order_packing']) {
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

        // Save order
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Save'),
                    'class' => 'btn btn-success',
                ],
            ]
        );

        $groups = [
            'order'             => [
                "label"    => __('Order'),
                "elements" => ['uid', 'status_order', 'time_order'],
            ],
            'delivery_address'  => [
                'label'    => __('Delivery address'),
                'elements' => ['delivery_first_name', 'delivery_last_name', 'delivery_email', 'delivery_mobile', 'delivery_address1', 'delivery_address2',
                               'delivery_city', 'delivery_zip_code', 'delivery_country', 'delivery_company', 'delivery_company_id', 'delivery_company_vat'],
            ],
            'invoicing_address' => [
                'label'    => __('Invoicing address'),
                'elements' => ['invoicing_first_name', 'invoicing_last_name', 'invoicing_email', 'invoicing_mobile', 'invoicing_address1', 'invoicing_address2',
                               'invoicing_city', 'invoicing_zip_code', 'invoicing_country', 'invoicing_company', 'invoicing_company_id',
                               'invoicing_company_vat'],
            ],
            'payment'           => [
                "label"    => __('Payment'),
                "elements" => ['default_gateway'],
            ],
        ];

        foreach ($groups as $key => &$group) {
            foreach ($group['elements'] as $key => &$field) {
                if (!array_key_exists($field, $this->getElements())) {
                    unset($group['elements'][$key]);
                }
            }
        }

        foreach ($groups as $key => &$group) {
            if (count($group['elements']) == 0) {
                unset($groups[$key]);
            }
        }
        $this->setGroups($groups);
    }
}

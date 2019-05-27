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

use Module\System\Validator\UserEmail as UserEmailValidator;
use Pi;
use Zend\InputFilter\InputFilter;

class OrderFilter extends InputFilter
{
    public function __construct($option = [])
    {
        // Get config
        $config = Pi::service('registry')->config->read('order', 'order');

        // Check for load register form
        $registerFiltersName = [];
        if (Pi::service('module')->isActive('user')
            && !Pi::service('authentication')->hasIdentity()
            && isset($_SESSION['session_order'])
            && !empty($_SESSION['session_order'])
        ) {
            $registerFilters = Pi::api('form', 'user')->loadFilters('register');
            foreach ($registerFilters as $filter) {
                $registerFiltersName[] = $filter['name'];
                $this->add($filter);
            }
        }
        $this->add(
            [
                'name'     => 'address_id',
                'required' => false,
            ]
        );
        // name
        if ($config['order_name']) {
            // first_name
            if (!in_array('first_name', $registerFiltersName)) {
                $this->add(
                    [
                        'name'     => 'first_name',
                        'required' => true,
                        'filters'  => [
                            [
                                'name' => 'StringTrim',
                            ],
                        ],
                    ]
                );
            }
            // last_name
            if (!in_array('last_name', $registerFiltersName)) {
                $this->add(
                    [
                        'name'     => 'last_name',
                        'required' => true,
                        'filters'  => [
                            [
                                'name' => 'StringTrim',
                            ],
                        ],
                    ]
                );
            }
        }
        // id_number
        if ($config['order_idnumber'] && !in_array('id_number', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'id_number',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // email
        if ($config['order_email'] && !in_array('email', $registerFiltersName)) {
            $this->add(
                [
                    'name'       => 'email',
                    'required'   => true,
                    'filters'    => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                    'validators' => [
                        [
                            'name'    => 'EmailAddress',
                            'options' => [
                                'useMxCheck'     => false,
                                'useDeepMxCheck' => false,
                                'useDomainCheck' => false,
                            ],
                        ],
                        new UserEmailValidator(
                            [
                                'blacklist'         => false,
                                'check_duplication' => false,
                            ]
                        ),
                    ],
                ]
            );
        }
        // phone
        if ($config['order_phone'] && !in_array('phone', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'phone',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // mobile
        if ($config['order_mobile'] && !in_array('mobile', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'mobile',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // company
        if ($config['order_company'] && !in_array('company', $registerFiltersName)) {
            // company
            $this->add(
                [
                    'name'     => 'company',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // company extra
        if ($config['order_company_extra']) {
            // company_id
            if (!in_array('company_id', $registerFiltersName)) {
                $this->add(
                    [
                        'name'     => 'company_id',
                        'required' => false,
                        'filters'  => [
                            [
                                'name' => 'StringTrim',
                            ],
                        ],
                    ]
                );
            }
            // company_vat
            if (!in_array('company_vat', $registerFiltersName)) {
                $this->add(
                    [
                        'name'     => 'company_vat',
                        'required' => false,
                        'filters'  => [
                            [
                                'name' => 'StringTrim',
                            ],
                        ],
                    ]
                );
            }
        }
        // address1
        if ($config['order_address1'] && !in_array('address1', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'address1',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // address 2
        if ($config['order_address2'] && !in_array('address2', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'address2',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // country
        if ($config['order_country'] && !in_array('country', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'country',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // state
        if ($config['order_state'] && !in_array('state', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'state',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // city
        if ($config['order_city'] && !in_array('city', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'city',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // zip_code
        if ($config['order_zip'] && !in_array('zip_code', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'zip_code',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // packing
        if ($config['order_packing'] && !in_array('packing', $registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'packing',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // Check type_commodity
        switch ($option['type_commodity']) {
            case 'product':
                if ($config['order_location_delivery']) {
                    // location
                    $this->add(
                        [
                            'name'     => 'location',
                            'required' => true,
                            'filters'  => [
                                [
                                    'name' => 'StringTrim',
                                ],
                            ],
                        ]
                    );
                    // delivery
                    $this->add(
                        [
                            'name'     => 'delivery',
                            'required' => true,
                            'filters'  => [
                                [
                                    'name' => 'StringTrim',
                                ],
                            ],
                        ]
                    );
                    // gateway
                    $this->add(
                        [
                            'name'     => 'gateway',
                            'required' => true,
                            'filters'  => [
                                [
                                    'name' => 'StringTrim',
                                ],
                            ],
                        ]
                    );
                } else {
                    // gateway
                    $this->add(
                        [
                            'name'     => 'gateway',
                            'required' => true,
                            'filters'  => [
                                [
                                    'name' => 'StringTrim',
                                ],
                            ],
                        ]
                    );
                }
                break;

            case 'service':
                // gateway
                $this->add(
                    [
                        'name'     => 'gateway',
                        'required' => true,
                        'filters'  => [
                            [
                                'name' => 'StringTrim',
                            ],
                        ],
                    ]
                );
                break;
        }
        // user_note
        if ($config['order_usernote']) {
            $this->add(
                [
                    'name'     => 'user_note',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // Update profile confirmation
        if ($config['order_update_user'] && empty($registerFiltersName)) {
            $this->add(
                [
                    'name'     => 'update_user',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // order_term
        if ($config['order_term'] && !empty($config['order_termurl'])) {
            $this->add(
                [
                    'name'       => 'order_term',
                    'required'   => true,
                    'filters'    => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                    'validators' => [
                        new \Module\Order\Validator\Term,
                    ],
                ]
            );
        }
    }
}
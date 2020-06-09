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
use Laminas\InputFilter\InputFilter;

class OrderUpdateFilter extends InputFilter
{
    public function __construct($option = [])
    {
        if ($option['mode'] == 'add') {

            $this->add(
                [
                    'name'       => 'uid',
                    'required'   => true,
                    'filters'    => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                    'validators' => [
                        new \Module\Order\Validator\User,
                    ],
                ]
            );

        }

        $this->add(
            [
                'name'     => 'time_order',
                'required' => true,
            ]
        );

        // name
        if ($option['config']['order_name']) {
            // first_name
            $this->add(
                [
                    'name'     => 'delivery_first_name',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            // last_name
            $this->add(
                [
                    'name'     => 'delivery_last_name',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );

            $this->add(
                [
                    'name'     => 'invoicing_first_name',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            // last_name
            $this->add(
                [
                    'name'     => 'invoicing_last_name',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }

        $this->add(
            [
                'name'     => 'default_gateway',
                'required' => true,
            ]
        );

        // id_number
        if ($option['config']['order_idnumber']) {
            $this->add(
                [
                    'name'     => 'delivery_id_number',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_id_number',
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
        if ($option['config']['order_email']) {
            $this->add(
                [
                    'name'       => 'delivery_email',
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
            $this->add(
                [
                    'name'       => 'invoicing_email',
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
        if ($option['config']['order_phone']) {
            $this->add(
                [
                    'name'     => 'delivery_phone',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_phone',
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
        if ($option['config']['order_mobile']) {
            $this->add(
                [
                    'name'     => 'delivery_mobile',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_mobile',
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
        if ($option['config']['order_company']) {
            // company
            $this->add(
                [
                    'name'     => 'delivery_company',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_company',
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
        if ($option['config']['order_company_extra']) {
            // company_id
            $this->add(
                [
                    'name'     => 'delivery_company_id',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            // company_vat
            $this->add(
                [
                    'name'     => 'delivery_company_vat',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );

            $this->add(
                [
                    'name'     => 'invoicing_company_id',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            // company_vat
            $this->add(
                [
                    'name'     => 'invoicing_company_vat',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // address
        if ($option['config']['order_address1']) {
            $this->add(
                [
                    'name'     => 'delivery_address1',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_address1',
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
        if ($option['config']['order_address2']) {
            $this->add(
                [
                    'name'     => 'delivery_address2',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_address2',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // country
        if ($option['config']['order_country']) {
            $this->add(
                [
                    'name'     => 'delivery_country',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_country',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // state
        if ($option['config']['order_state']) {
            $this->add(
                [
                    'name'     => 'delivery_state',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_state',
                    'required' => false,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }
        // city
        if ($option['config']['order_city']) {
            $this->add(
                [
                    'name'     => 'delivery_city',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_city',
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
        if ($option['config']['order_zip']) {
            $this->add(
                [
                    'name'     => 'delivery_zip_code',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
            $this->add(
                [
                    'name'     => 'invoicing_zip_code',
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
        if ($option['config']['order_packing']) {
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
    }
}

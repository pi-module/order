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

class AddressFilter extends InputFilter
{
    public function __construct($option = [])
    {
        // Get config
        $config = Pi::service('registry')->config->read('order');

        // account_type
        $this->add(
            [
                'name'     => 'account_type',
                'required' => true,
            ]
        );

        // address_id
        $this->add(
            [
                'name'     => 'address_id',
                'required' => false,
            ]
        );

        // company
        if ($config['order_company']) {
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

        // address1
        if ($config['order_address1']) {
            $this->add(
                [
                    'name'     => 'company_address1',
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
        if ($config['order_address2']) {
            $this->add(
                [
                    'name'     => 'company_address2',
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
        if ($config['order_country']) {
            $this->add(
                [
                    'name'     => 'company_country',
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
        if ($config['order_state']) {
            $this->add(
                [
                    'name'     => 'company_state',
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
        if ($config['order_city']) {
            $this->add(
                [
                    'name'     => 'company_city',
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
        if ($config['order_zip']) {
            $this->add(
                [
                    'name'     => 'company_zip_code',
                    'required' => true,
                    'filters'  => [
                        [
                            'name' => 'StringTrim',
                        ],
                    ],
                ]
            );
        }

        // name
        if ($config['order_name']) {
            // first_name
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

            // last_name
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

        // id_number
        if ($config['order_idnumber']) {
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
        if ($config['order_email']) {
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
        if ($config['order_phone']) {
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
        if ($config['order_mobile']) {
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

        // company extra
        if ($config['order_company_extra']) {
            // company_id
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

            // company_vat
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

        // address1
        if ($config['order_address1']) {
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
        if ($config['order_address2']) {
            $this->add(
                [
                    'name'     => 'address2',
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
        if ($config['order_country']) {
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
        if ($config['order_state']) {
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
        if ($config['order_city']) {
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
        if ($config['order_zip']) {
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

        // birthday
        if ($config['order_birthday']) {
            $this->add(
                [
                    'name'     => 'birthday',
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

    public function isValid($context = null)
    {
        foreach (['company', 'company_address1', 'company_zip_code', 'company_city', 'company_state', 'company_country', 'company_id', 'company_vat'] as $name)
        {
            if ($this->has($name)) {
                $this->get($name)->setRequired($this->data['account_type'] != 'individual');
            }
        }

        return parent::isValid($context);
    }

}

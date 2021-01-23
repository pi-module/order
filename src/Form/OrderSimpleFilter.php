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
use Laminas\InputFilter\InputFilter;

class OrderSimpleFilter extends InputFilter
{
    public function __construct($option = [])
    {
        $config = Pi::service('registry')->config->read('order');

        $this->add(
            [
                'name'     => 'address_delivery_id',
                'required' => false,
            ]
        );
        $this->add(
            [
                'name'     => 'address_invoicing_id',
                'required' => false,
            ]
        );

        // packing
        if ($config['order_packing']) {
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
                            'name'     => 'default_gateway',
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
                            'name'     => 'default_gateway',
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
            case 'booking':
            case 'service':
                // gateway
                $this->add(
                    [
                        'name'     => 'default_gateway',
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
        // order_term
        if ($config['order_term']) {
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

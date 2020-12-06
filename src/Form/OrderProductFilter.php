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

class OrderProductFilter extends InputFilter
{
    public function __construct($option = [])
    {
        $this->add(
            [
                'name'     => 'module',
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
                'name'     => 'product_type',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );

        // id
        $this->add(
            [
                'name'     => 'product',
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
                'name'     => 'module_item',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );

        // product_price
        $this->add(
            [
                'name'     => 'product_price',
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
                'name'     => 'discount_price',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );
        // shipping_price
        $this->add(
            [
                'name'     => 'shipping_price',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );
        // packing_price
        $this->add(
            [
                'name'     => 'packing_price',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );
        // setup_price
        $this->add(
            [
                'name'     => 'setup_price',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );
        // vat_price
        $this->add(
            [
                'name'     => 'vat_price',
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
                'name'     => 'time_start',
                'required' => false,
            ]
        );

        $this->add(
            [
                'name'     => 'time_end',
                'required' => false,
            ]
        );

        $this->add(
            [
                'name'     => 'admin_note',
                'required' => false,
            ]
        );

        // extra options
        foreach (['order', 'shop', 'guide', 'event', 'video', 'plans'] as $module) {
            if (Pi::service('module')->isActive($module)) {
                $elems = Pi::api('order', $module)->getExtraFieldsFormForOrder();
                foreach ($elems as $elem) {
                    $this->add(
                        [
                            'name'     => $elem['name'],
                            'required' => false,
                        ]
                    );
                }
            }
        }
    }
}
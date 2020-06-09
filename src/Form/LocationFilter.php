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

class LocationFilter extends InputFilter
{
    public function __construct($option = [])
    {
        // set delivery
        $this->delivery = $option['delivery'];
        // id
        $this->add(
            [
                'name'     => 'id',
                'required' => false,
            ]
        );
        // title
        $this->add(
            [
                'name'     => 'title',
                'required' => true,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ]
        );
        // parent
        $this->add(
            [
                'name'     => 'parent',
                'required' => true,
            ]
        );
        // status
        $this->add(
            [
                'name'     => 'status',
                'required' => true,
            ]
        );
        // delivery
        foreach ($this->delivery as $delivery) {
            // active
            $this->add(
                [
                    'name'     => sprintf('delivery_active_%s', $delivery['id']),
                    'required' => false,
                ]
            );
            // price
            $this->add(
                [
                    'name'     => sprintf('delivery_price_%s', $delivery['id']),
                    'required' => false,
                ]
            );
            // delivery_time
            $this->add(
                [
                    'name'     => sprintf('delivery_time_%s', $delivery['id']),
                    'required' => false,
                ]
            );
        }
    }
}
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

class PromocodeFilter extends InputFilter
{
    public function __construct($option = [])
    {
        $this->add(
            [
                'name'     => 'id',
                'required' => false,
            ]
        );
        $this->add(
            [
                'name'       => 'code',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'Db/NoRecordExists',
                        'options' => [
                            'table'   => Pi::model('promocode', 'order')->getTable(),
                            'field'   => 'code',
                            'adapter' => Pi::model('promocode', 'order')->getAdapter(),
                            'exclude' => [
                                'field' => 'id',
                                'value' => isset($option['id']) ? $option['id'] : null,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'promo',
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'IsInt',
                    ],
                    [
                        'name'    => 'Between',
                        'options' => ['min' => 0, 'max' => 100],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'     => 'time_start',
                'required' => true,
            ]
        );

        $this->add(
            [
                'name'     => 'time_end',
                'required' => true,
            ]
        );

        $this->add(
            [
                'name'     => 'module',
                'required' => true,
            ]
        );

        $this->add(
            [
                'name'     => 'showcode',
                'required' => true,
            ]
        );
    }
}

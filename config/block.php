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
return [
    'detail'      => [
        'name'        => 'detail',
        'title'       => _a('Orders detail'),
        'description' => _a('Simple orders detail'),
        'render'      => ['block', 'detail'],
        'template'    => 'detail',
    ],
    'installment' => [
        'name'        => 'installment',
        'title'       => _a('Installment'),
        'description' => _a('Installment orders detail'),
        'render'      => ['block', 'installment'],
        'template'    => 'installment',
    ],
    'credit'      => [
        'name'        => 'credit',
        'title'       => _a('Credit'),
        'description' => _a('User credit information'),
        'render'      => ['block', 'credit'],
        'template'    => 'credit',
    ],
];

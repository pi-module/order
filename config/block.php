<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
return array(
    'detail' => array(
        'name' => 'detail',
        'title' => _a('Orders detail'),
        'description' => _a('Simple orders detail'),
        'render' => array('block', 'detail'),
        'template' => 'detail',
    ),
    'installment' => array(
        'name' => 'installment',
        'title' => _a('Installment'),
        'description' => _a('Installment orders detail'),
        'render' => array('block', 'installment'),
        'template' => 'installment',
    ),
    'credit' => array(
        'name' => 'credit',
        'title' => _a('Credit'),
        'description' => _a('User credit information'),
        'render' => array('block', 'credit'),
        'template' => 'credit',
    ),
);
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
    // Admin section
    'admin' => array(
        array(
            'label'         => _a('Product'),
            'controller'    => 'product',
            'permission'    => 'product',
        ),
        array(
            'label'         => _a('Category'),
            'controller'    => 'category',
            'permission'    => 'category',
        ),
        array(
            'label'         => _a('Attribute'),
            'controller'    => 'attribute',
            'permission'    => 'attribute',
        ),
        array(
            'label'         => _a('Attach'),
            'controller'    => 'attach',
            'permission'    => 'attach',
        ),
        array(
            'label'         => _a('Orders'),
            'controller'    => 'order',
            'permission'    => 'order',
        ),
        array(
            'label'         => _a('Checkout'),
            'controller'    => 'checkout',
            'permission'    => 'checkout',
        ),
        array(
            'label'         => _a('Logs'),
            'controller'    => 'log',
            'permission'    => 'log',
        ),
        array(
            'label'         => _a('Tools'),
            'controller'    => 'tools',
            'permission'    => 'tools',
        ),
    ),
    // Front section
    'front' => array(
        array(
            'title'         => _a('Index page'),
            'controller'    => 'index',
            'permission'    => 'public',
            'block'         => 1,
        ),
        array(
            'label'         => _a('Category'),
            'controller'    => 'category',
            'permission'    => 'public',
            'block'         => 1,
        ),
        array(
            'label'         => _a('Product'),
            'controller'    => 'product',
            'permission'    => 'public',
            'block'         => 1,
        ),
        
        array(
            'label'         => _a('Tags'),
            'controller'    => 'tag',
            'permission'    => 'public',
            'block'         => 1,
        ),
        array(
            'label'         => _a('User panel'),
            'controller'    => 'user',
            'permission'    => 'user',
            'block'         => 1,
        ),
        array(
            'label'         => _a('Search'),
            'controller'    => 'search',
            'permission'    => 'search',
            'block'         => 1,
        ),
        array(
            'label'         => _a('Checkout'),
            'controller'    => 'checkout',
            'permission'    => 'checkout',
            'block'         => 1,
        ),
        array(
            'label'         => _a('Category list'),
            'controller'    => 'category',
            'action'        => 'list',
            'permission'    => 'public',
            'block'         => 1,
        ),
        array(
            'label'         => _a('Tag list'),
            'controller'    => 'tag',
            'action'        => 'list',
            'permission'    => 'public',
            'block'         => 1,
        ),
    ),
);
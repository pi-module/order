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
    // Module meta
    'meta' => array(
        'title' => _a('Orders'),
        'description' => _a('Manage order process and payment'),
        'version' => '2.2.3',
        'license' => 'New BSD',
        'logo' => 'image/logo.png',
        'readme' => 'docs/readme.txt',
        'demo' => 'http://pialog.org',
        'icon' => 'fa-money-bill-alt',
    ),
    // Author information
    'author' => array(
		'Dev'      => 'Hossein Azizabadi; Marc Desrousseaux; Frederic Tissot; Mickael Stamm',
        'Fonctionnal Design'     => '@marc-pi, @voltan',
        'QA'        => '@marc-pi',
        'website' => 'http://pialog',
        'credits' => 'Pi Engine Team'
    ),
    // Resource
    'resource' => array(
        'database' => 'database.php',
        'config' => 'config.php',
        'permission' => 'permission.php',
        'page' => 'page.php',
        'navigation' => 'navigation.php',
        'block' => 'block.php',
        'route' => 'route.php',
    ),
);
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
    // Module meta
    'meta'     => [
        'title'       => _a('Orders'),
        'description' => _a('Manage order process and payment'),
        'version'     => '2.3.4',
        'license'     => 'New BSD',
        'logo'        => 'image/logo.png',
        'readme'      => 'docs/readme.txt',
        'demo'        => 'http://piengine.org',
        'icon'        => 'fa-money-bill-alt',
    ],
    // Author information
    'author'   => [
        'Dev'                => 'Hossein Azizabadi; Marc Desrousseaux; Frederic Tissot; Mickael Stamm',
        'Fonctionnal Design' => '@marc-pi, @voltan',
        'QA'                 => '@marc-pi',
        'website'            => 'http://piengine.org',
        'credits'            => 'Pi Engine Team',
    ],
    // Resource
    'resource' => [
        'database'   => 'database.php',
        'config'     => 'config.php',
        'permission' => 'permission.php',
        'page'       => 'page.php',
        'navigation' => 'navigation.php',
        'block'      => 'block.php',
        'route'      => 'route.php',
    ],
];

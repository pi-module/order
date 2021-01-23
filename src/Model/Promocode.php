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

namespace Module\Order\Model;

use Pi\Application\Model\Model;

class Promocode extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'code',
            'promo',
            'time_start',
            'time_end',
            'module',
            'showcode',
        ];

    public function getModules()
    {
        return [
            'guide' => 'guide',
            'event' => 'event',
            'video' => 'video',
            'shop'  => 'shop',
        ];
    }
}

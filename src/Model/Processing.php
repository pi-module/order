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

class Processing extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'uid',
            'ip',
            'order',
            'random_id',
            'gateway',
            'time_create',
        ];
}

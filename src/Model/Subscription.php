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

class Subscription extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'uid',
            'order',
            'subscription_id',
            'subscription_plan',
            'subscription_interval',
            'subscription_status',
            'subscription_create_time',
            'current_period_start',
            'current_period_end',
            'time_create',
        ];
}

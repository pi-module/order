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

namespace Module\Order\Model\Subscription;

use Pi\Application\Model\Model;

class Plan extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'stripe_product_id',
            'stripe_price_id',
            'product_id',
            'product_name',
            'product_type',
            'module',
            'amount',
            'interval',
        ];
}
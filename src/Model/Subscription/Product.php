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

class Product extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'stripe_product_id',
            'stripe_price_id',
            'service_id',
            'service_title',
            'service_module',
            'service_amount',
            'service_interval',
        ];
}
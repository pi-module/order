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

class Detail extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'order',
            'module',
            'product_type',
            'product',
            'time_create',
            'time_start',
            'time_end',
            'product_price',
            'discount_price',
            'shipping_price',
            'packing_price',
            'setup_price',
            'vat_price',
            'promotion_code',
            'number',
            'extra',
            'admin_note',
        ];
}

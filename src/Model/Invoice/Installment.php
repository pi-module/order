<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */

namespace Module\Order\Model\Invoice;

use Pi\Application\Model\Model;

class Installment extends Model
{
    const STATUS_PAYMENT_UNPAID = 1;
    const STATUS_PAYMENT_PAID   = 2;


    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'invoice',
            'count',
            'gateway',
            'status_payment',
            'time_payment',
            'time_duedate',
            'due_price',
            'credit_price',
            'comment',
            'extra'
        ];

    public static function getStatusList()
    {
        return [
            self::STATUS_PAYMENT_UNPAID => __('Unpaid'),
            self::STATUS_PAYMENT_PAID   => __('Paid'),
        ];
    }
}

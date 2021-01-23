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

class Invoice extends Model
{
    const STATUS_INVOICE_DRAFT     = 0;
    const STATUS_INVOICE_VALIDATED = 1;
    const STATUS_INVOICE_CANCELLED = 2;

    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'random_id',
            'type_payment',
            'order',
            'code',
            'status',
            'time_create',
            'time_cancel',
            'time_invoice',
            'back_url',
            'create_by',
            'type',
            'extra',
        ];

    public static function getStatusList($status = 0)
    {
        $statusList = [
            Invoice::STATUS_INVOICE_DRAFT     => __('Draft'),
            Invoice::STATUS_INVOICE_VALIDATED => __('Validated'),
            Invoice::STATUS_INVOICE_CANCELLED => __('Cancelled'),
        ];

        if ($status == Invoice::STATUS_INVOICE_VALIDATED) {
            unset($statusList[Invoice::STATUS_INVOICE_DRAFT]);
        }

        if ($status == Invoice::STATUS_INVOICE_CANCELLED) {
            unset($statusList[Invoice::STATUS_INVOICE_DRAFT]);
            unset($statusList[Invoice::STATUS_INVOICE_VALIDATED]);
        }

        return $statusList;
    }
}

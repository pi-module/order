<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */

namespace Module\Order\Model\Order;

use Pi\Application\Model\Model;

class Installment extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'order',
        'count',
        'gateway',
        'status_payment',
        'time_payment',
        'paid_price',
    );
}

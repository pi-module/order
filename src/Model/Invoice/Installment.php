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

namespace Module\Order\Model\Invoice;

use Pi\Application\Model\Model;

class Installment extends Model
{
    const STATUS_PAYMENT_UNPAID = 1;
    const STATUS_PAYMENT_PAID = 2;
    
    
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'invoice',
        'count',
        'gateway',
        'status_payment',
        'time_payment',
        'time_duedate',
        'due_price',
        'credit_price',
    );
    
    public static function getStatusList()
    {
        return array(
            self::STATUS_PAYMENT_UNPAID => __('Unpaid'),
            self::STATUS_PAYMENT_PAID => __('Paid'),
        );        
    }
}

<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Model;

use Pi\Application\Model\Model;

class Invoice extends Model
{
    const STATUS_INVOICE_DRAFT                = 0;
    const STATUS_INVOICE_VALIDATED            = 1;
    const STATUS_INVOICE_CANCELLED            = 2;
    
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'random_id',
        'type_payment',
        'order',
        'code',
        'status',
        'time_create',
        'time_cancel',
        'back_url',
        'create_by',
        'type'
    );
    
    public static function getStatusList($status = 0) 
    {
        $statusList = array(
            Invoice::STATUS_INVOICE_DRAFT => __('Draft'),
            Invoice::STATUS_INVOICE_VALIDATED  => __('Validated'),
            Invoice::STATUS_INVOICE_CANCELLED => __('Cancelled')
        );
        
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

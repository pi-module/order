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

class Order extends Model
{
    const STATUS_ORDER_DRAFT                = 0;
    const STATUS_ORDER_VALIDATED            = 1;
    const STATUS_ORDER_CANCELLED            = 2;
    
    const STATUS_DELIVERY_PENDING             = 1;
    const STATUS_DELIVERY_PACKED              = 2;
    const STATUS_DELIVERY_POSTED              = 3;
    const STATUS_DELIVERY_DELIVERED           = 4;
    const STATUS_DELIVERY_BACK                = 5;
           
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'uid',
        'code',
        'type_commodity',
        'can_pay',
        'plan',
        'ip',
        'user_note',
        'admin_note',
        'time_create',
        'time_delivery',
        'status_order',
        'status_delivery',
        'packing',
        'promotion_type',
        'promotion_value',
        'create_by'
    );
    
    public static function getStatusList($onlyValidated = false) 
    {
        if ($onlyValidated) {
            return array(
                Order::STATUS_ORDER_VALIDATED  => __('Validated'),
            );  
        }
        return array(
            Order::STATUS_ORDER_DRAFT => __('Draft'),
            Order::STATUS_ORDER_VALIDATED  => __('Validated'),
            Order::STATUS_ORDER_CANCELLED => __('Cancelled')
        );
    }
}

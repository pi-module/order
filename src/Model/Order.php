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
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id', 'uid', 'code', 'type_payment', 'type_commodity', 'plan', 'module_name', 'module_table', 'module_item',
        'ip', 'id_number', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2',
        'country', 'state', 'city', 'zip_code', 'company', 'company_id', 'company_vat', 'user_note',
        'admin_note', 'time_create', 'time_payment', 'time_delivery', 'time_finish', 'time_start',
        'time_end', 'status_order', 'status_payment', 'status_delivery', 'product_price',
        'discount_price', 'shipping_price', 'packing_price', 'vat_price', 'total_price',
        'paid_price', 'gateway', 'delivery', 'location', 'packing', 'promo_type', 'promo_value'
    );
}

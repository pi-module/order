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

class Customer extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id', 'uid', 'ip', 'id_number', 'first_name', 'last_name', 'email',
        'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city',
        'zip_code', 'company', 'company_id', 'company_vat', 'user_note',
        'time_create', 'time_update', 'status', 'address_type', 'delivery', 'location'
    );
}
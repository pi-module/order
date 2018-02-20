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

namespace Module\Order\Model;

use Pi\Application\Model\Model;

class OrderAddress extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'order',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'address1',
        'address2',
        'country',
        'state',
        'city',
        'zip_code',
        'company',
        'company_id',
        'company_vat',
        'delivery',
        'location',
    );
}

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

namespace Module\Order\Model\Order;

use Pi\Application\Model\Model;

class Address extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns
        = [
            'id',
            'order',
            'id_number',
            'type',
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
            'birthday',
            'company',
            'company_id',
            'company_vat',
            'delivery',
            'location',
            'account_type',
            'company_address1',
            'company_address2',
            'company_country',
            'company_state',
            'company_city',
            'company_zip_code',
        ];
}

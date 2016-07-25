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

class History extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'uid',
        'time_create',
        'order',
        'invoice',
        'amount',
        'amount_old',
        'status',
        'status_fluctuation',
        'status_action',
        'message_user',
        'message_admin',
        'ip',
    );
}
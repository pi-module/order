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

class Processing extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'uid',
        'ip',
        'order',
        'random_id',
        'gateway',
        'time_create',
    );
}
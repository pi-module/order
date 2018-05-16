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

class Promocode extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'code',
        'promo',
        'time_start',
        'time_end',
        'module',
        'showcode',
    );
    
    public function getModules()
    {
       return array(
            'guide' => 'guide', 
            'event' => 'event'
       );
    }
}

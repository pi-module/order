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

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Json\Json;

class Promocode extends AbstractApi
{
    public function get($code)
    {
        $select = Pi::model('promocode', 'order')->select()->where(array('code' => $code));
        $row = Pi::model('promocode', 'order')->selectWith($select)->current();
        return $row;        
    }
    
    public function hasActiveCode()
    {
        $where = array(
            'time_start <= ' . strtotime(date('Y-m-d')),
            'time_end >= ' . strtotime(date('Y-m-d')),
        );
        
        $select = Pi::model('promocode', 'order')->select()->where($where);
        $count = Pi::model('promocode', 'order')->selectWith($select)->count();
        return $count;        
    }
}
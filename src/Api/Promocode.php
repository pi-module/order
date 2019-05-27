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
            'time_start <= ' . time(),
            'time_end >= ' . time(),
        );
        
        $select = Pi::model('promocode', 'order')->select()->where($where);
        $count = Pi::model('promocode', 'order')->selectWith($select)->count();
        return $count;        
    }

    public function getLastActiveCode(){
        $model = Pi::model('promocode', 'order');
        $select = $model->select();

        $select->where(array(
            'time_start <= ' . time(),
            'time_end >= ' . time(),
        ));

        $select->order('id DESC');

        $result = Pi::model('promocode', 'order')->selectWith($select)->current();

        return $result;
    }
}
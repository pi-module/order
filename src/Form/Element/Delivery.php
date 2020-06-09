<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

namespace Module\Order\Form\Element;

use Pi;
use Laminas\Form\Element\Select;

class Delivery extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $list   = [];
            $where  = ['location' => $this->options['location']];
            $select = Pi::model('location_delivery', 'order')->select()->where($where);
            $rowset = Pi::model('location_delivery', 'order')->selectWith($select);
            foreach ($rowset as $row) {
                $delivery = Pi::model('delivery', 'order')->find($row->delivery)->toArray();
                if ($delivery['status']) {
                    $list[$delivery['id']] = sprintf(
                        '%s - %s : %s - %s : %s %s',
                        $delivery['title'],
                        __('Price'),
                        Pi::api('api', 'order')->viewPrice($row->price),
                        __('Time'),
                        _number($row->delivery_time),
                        __('Days')
                    );
                }
            }
            $this->valueOptions = $list;
        }
        return $this->valueOptions;
    }
}
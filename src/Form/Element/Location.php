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

namespace Module\Order\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class Location extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            if (isset($this->options['parent'])
                && $this->options['parent']
            ) {
                $list[0] = '';
            }
            $select = Pi::model('location', 'order')->select();
            $rowset = Pi::model('location', 'order')->selectWith($select);
            foreach ($rowset as $row) {
                $list[$row->id] = $row->title;
            }
            $this->valueOptions = $list;
        }
        return $this->valueOptions;
    }
}
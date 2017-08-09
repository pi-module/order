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

namespace Module\Order\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class OrderProductDeleteFilter extends InputFilter
{
    public function __construct($option = array())
    {
        // id
        $this->add(array(
            'name' => 'id',
            'required' => true,
        ));
        // invoice
        $this->add(array(
            'name' => 'invoice',
            'required' => true,
        ));
        // order
        $this->add(array(
            'name' => 'order',
            'required' => true,
        ));
        // count
        $this->add(array(
            'name' => 'count',
            'required' => true,
        ));
        // confirm
        $this->add(array(
            'name' => 'confirm',
            'required' => true,
        ));
    }
}
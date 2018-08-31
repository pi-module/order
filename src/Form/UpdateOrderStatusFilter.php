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

class UpdateOrderStatusFilter extends InputFilter
{
    public function __construct()
    {
        // status_order
        $this->add(array(
            'name' => 'status_order',
            'required' => true,
        ));
    }
}    	
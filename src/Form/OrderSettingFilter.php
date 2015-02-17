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

namespace Module\Shop\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class OrderSettingFilter extends InputFilter
{
    public function __construct()
    {
    	// status_order
        $this->add(array(
            'name' => 'status_order',
            'required' => true,
        ));
        // status_payment
        $this->add(array(
            'name' => 'status_payment',
            'required' => true,
        ));
        // status_delivery
        $this->add(array(
            'name' => 'status_delivery',
            'required' => true,
        ));
    }
}    	
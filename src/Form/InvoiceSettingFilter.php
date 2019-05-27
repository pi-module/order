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

namespace Module\Order\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class InvoiceSettingFilter extends InputFilter
{
    public function __construct()
    {
        // orderid
        $this->add(array(
            'name' => 'orderid',
            'required' => false,
        ));
        // randomid
        $this->add(array(
            'name' => 'randomid',
            'required' => false,
        ));
        // payment_status
        $this->add(array(
            'name' => 'payment_status',
            'required' => false,
        ));
        // start
        $this->add(array(
            'name' => 'start',
            'required' => false,
        ));
        // end
        $this->add(array(
            'name' => 'end',
            'required' => false,
        ));
    }
}    	
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

class UpdatePaymentFilter extends InputFilter
{
    public function __construct()
    {
        // status_payment
        $this->add(array(
            'name' => 'status_payment',
            'required' => true,
        ));
        // payment_adapter
        $this->add(array(
            'name' => 'payment_adapter',
            'required' => true,
        ));
    }
}    	
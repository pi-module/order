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

class InvoiceFilter extends InputFilter
{
    public function __construct()
    {
        // product_price
        $this->add(array(
            'name' => 'type_payment',
            'required' => false,
            
        ));
         $this->add(array(
            'name' => 'time_invoice',
            'required' => false,
            
        ));
    }
}

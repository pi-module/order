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

class InstallmentFilter extends InputFilter
{
    public function __construct($options = array())
    {
        $this->add(array(
            'name' => 'gateway',
            'required' => $options['readonly'] ? false : true,
            
        ));
        
        $this->add(array(
            'name' => 'status_payment',
            'required' => $options['readonly'] ? false : true,
            
        ));
        $this->add(array(
            'name' => 'time_duedate',
            'required' => true,
            
        ));
        $this->add(array(
            'name' => 'time_payment',
            'required' => true,
        ));
        
        $this->add(array(
            'name' => 'comment',
            'required' => false,
        ));
        
    }
}
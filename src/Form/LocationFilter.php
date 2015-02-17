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

class LocationFilter extends InputFilter
{
    public function __construct($option = array())
    {
        // set delivery
        $this->delivery = $option['delivery'];
        // id
        $this->add(array(
            'name' => 'id',
            'required' => false,
        ));
        // title
        $this->add(array(
            'name' => 'title',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // parent
        $this->add(array(
            'name' => 'parent',
            'required' => true,
        ));
        // status
        $this->add(array(
            'name' => 'status',
            'required' => true,
        ));
        // delivery
        foreach ($this->delivery as $delivery) {
        	// active
        	$this->add(array(
                'name' => sprintf('delivery_active_%s', $delivery['id']),
                'required' => false,
            ));
        	// price
        	$this->add(array(
                'name' => sprintf('delivery_price_%s', $delivery['id']),
                'required' => false,
            ));
        	// delivery_time
        	$this->add(array(
                'name' => sprintf('delivery_time_%s', $delivery['id']),
                'required' => false,
            ));
        }
    }
}
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

class OrderProductFilter extends InputFilter
{
    public function __construct($option = array())
    {
        $this->add(array(
            'name' => 'module',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'product_type',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        // id
        $this->add(array(
            'name' => 'product',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'module_item',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        // product_price
        $this->add(array(
            'name' => 'product_price',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // shipping_price
        $this->add(array(
            'name' => 'shipping_price',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // packing_price
        $this->add(array(
            'name' => 'packing_price',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // setup_price
        $this->add(array(
            'name' => 'setup_price',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // vat_price
        $this->add(array(
            'name' => 'vat_price',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'time_start',
            'required' => false,
        ));
        
        $this->add(array(
            'name' => 'time_end',
            'required' => false,
        ));
        
        // extra options
        foreach (array('order', 'shop', 'guide', 'event') as $module) {
            if (Pi::service('module')->isActive($module)) {
                $elems = Pi::api('order', $module)->getExtraFieldsFormForOrder();
                foreach ($elems as $elem) {
                    $this->add(array(
                        'name' => $elem['name'],
                        'required' => false,
                    ));     
                }
            }
        } 
    }
}
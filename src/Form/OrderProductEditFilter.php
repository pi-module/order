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

class OrderProductEditFilter extends InputFilter
{
    public function __construct($option = array())
    {
        // id
        $this->add(array(
            'name' => 'id',
            'required' => true,
        ));
        // order
        $this->add(array(
            'name' => 'order',
            'required' => true,
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
        // number
        $this->add(array(
            'name' => 'number',
            'required' => true,
        ));
        // invoice
        $this->add(array(
            'name' => 'invoice',
            'required' => true,
        ));
    }
}
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
        // can_pay
        $this->add(array(
            'name' => 'can_pay',
            'required' => true,
        ));
        // type_payment
        $this->add(array(
            'name' => 'type_payment',
            'required' => true,
        ));
        // type_commodity
        $this->add(array(
            'name' => 'type_commodity',
            'required' => true,
        ));
        // code
        $this->add(array(
            'name' => 'code',
            'required' => false,
        ));
        // mobile
        $this->add(array(
            'name' => 'mobile',
            'required' => false,
        ));
        // email
        $this->add(array(
            'name' => 'email',
            'required' => false,
        ));
        // city
        $this->add(array(
            'name' => 'city',
            'required' => false,
        ));
        // uid
        $this->add(array(
            'name' => 'uid',
            'required' => false,
        ));
        // id_number
        $this->add(array(
            'name' => 'id_number',
            'required' => false,
        ));
        // first_name
        $this->add(array(
            'name' => 'first_name',
            'required' => false,
        ));
        // last_name
        $this->add(array(
            'name' => 'last_name',
            'required' => false,
        ));
        // zip_code
        $this->add(array(
            'name' => 'zip_code',
            'required' => false,
        ));
        // company
        $this->add(array(
            'name' => 'company',
            'required' => false,
        ));
    }
}    	
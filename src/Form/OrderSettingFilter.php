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
use Laminas\InputFilter\InputFilter;

class OrderSettingFilter extends InputFilter
{
    public function __construct()
    {
        // status_order
        $this->add(
            [
                'name'     => 'status_order',
                'required' => true,
            ]
        );
        // status_payment
        $this->add(
            [
                'name'     => 'status_payment',
                'required' => true,
            ]
        );
        // status_delivery
        $this->add(
            [
                'name'     => 'status_delivery',
                'required' => true,
            ]
        );
        // can_pay
        $this->add(
            [
                'name'     => 'can_pay',
                'required' => true,
            ]
        );
        // type_payment
        $this->add(
            [
                'name'     => 'type_payment',
                'required' => true,
            ]
        );
        // type_commodity
        $this->add(
            [
                'name'     => 'type_commodity',
                'required' => true,
            ]
        );
        // code
        $this->add(
            [
                'name'     => 'code',
                'required' => false,
            ]
        );
        // mobile
        $this->add(
            [
                'name'     => 'mobile',
                'required' => false,
            ]
        );
        // email
        $this->add(
            [
                'name'     => 'email',
                'required' => false,
            ]
        );
        // city
        $this->add(
            [
                'name'     => 'city',
                'required' => false,
            ]
        );
        // uid
        $this->add(
            [
                'name'     => 'uid',
                'required' => false,
            ]
        );
        // id_number
        $this->add(
            [
                'name'     => 'id_number',
                'required' => false,
            ]
        );
        // first_name
        $this->add(
            [
                'name'     => 'first_name',
                'required' => false,
            ]
        );
        // last_name
        $this->add(
            [
                'name'     => 'last_name',
                'required' => false,
            ]
        );
        // zip_code
        $this->add(
            [
                'name'     => 'zip_code',
                'required' => false,
            ]
        );
        // company
        $this->add(
            [
                'name'     => 'company',
                'required' => false,
            ]
        );
    }
}    	
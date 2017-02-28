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
use Module\System\Validator\UserEmail as UserEmailValidator;

class OrderSimpleFilter extends InputFilter
{
    public function __construct($option = array())
    {
        $config = Pi::service('registry')->config->read('order', 'order');
        // customer_id
        $this->add(array(
            'name' => 'customer_id',
            'required' => false,
        ));
        // id_number
        if ($config['order_idnumber']) {
            $this->add(array(
                'name' => 'id_number',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // packing
        if ($config['order_packing']) {
            $this->add(array(
                'name' => 'packing',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // Check type_commodity
        switch ($option['type_commodity']) {
            case 'product':
                if ($config['order_location_delivery']) {
                    // location
                    $this->add(array(
                        'name' => 'location',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'StringTrim',
                            ),
                        ),
                    ));
                    // delivery
                    $this->add(array(
                        'name' => 'delivery',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'StringTrim',
                            ),
                        ),
                    ));
                    // gateway
                    $this->add(array(
                        'name' => 'gateway',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'StringTrim',
                            ),
                        ),
                    ));
                } else {
                    // gateway
                    $this->add(array(
                        'name' => 'gateway',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'StringTrim',
                            ),
                        ),
                    ));
                }
                break;

            case 'service':
                // gateway
                $this->add(array(
                    'name' => 'gateway',
                    'required' => true,
                    'filters' => array(
                        array(
                            'name' => 'StringTrim',
                        ),
                    ),
                ));
                break;
        }
        // user_note
        if ($config['order_usernote']) {
            $this->add(array(
                'name' => 'user_note',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // order_term
        if ($config['order_term']) {
            $this->add(array(
                'name' => 'order_term',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
                'validators' => array(
                    new \Module\Order\Validator\Term,
                ),
            ));
        }
    }
}    	
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

class OrderFilter extends InputFilter
{
    public function __construct($option = array())
    {
        $config = Pi::service('registry')->config->read('order', 'order');
        //$checkout = Pi::api('order', 'order')->checkoutConfig();
        // customer_id
        $this->add(array(
            'name' => 'customer_id',
            'required' => false,
        ));
        // name
        if ($config['order_name']) {
            // first_name
            $this->add(array(
                'name' => 'first_name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // last_name
            $this->add(array(
                'name' => 'last_name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
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
        // email
        if ($config['order_email']) {
            $this->add(array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
                'validators'    => array(
                    array(
                        'name'      => 'EmailAddress',
                        'options'   => array(
                            'useMxCheck'        => false,
                            'useDeepMxCheck'    => false,
                            'useDomainCheck'    => false,
                        ),
                    ),
                    new UserEmailValidator(array(
                        'blacklist'         => false,
                        'check_duplication' => false,
                    )),
                ),
            ));
        }
        // phone
        if ($config['order_phone']) {
            $this->add(array(
                'name' => 'phone',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // mobile
        if ($config['order_mobile']) {
            $this->add(array(
                'name' => 'mobile',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // company
        if ($config['order_company']) {
            // company
            $this->add(array(
                'name' => 'company',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // company extra
        if ($config['order_company_extra']) {
            // company_id
            $this->add(array(
                'name' => 'company_id',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // company_vat
            $this->add(array(
                'name' => 'company_vat',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // address
        if ($config['order_address']) {
            $this->add(array(
                'name' => 'address1',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // address 2
        if ($config['order_address2']) {
            $this->add(array(
                'name' => 'address2',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // country
        if ($config['order_country']) {
            $this->add(array(
                'name' => 'country',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // state
        if ($config['order_state']) {
            $this->add(array(
                'name' => 'state',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // city
        if ($config['order_city']) {
            $this->add(array(
                'name' => 'city',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // zip_code
        if ($config['order_zip']) {
            $this->add(array(
                'name' => 'zip_code',
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
        // Update profile confirmation
        if ($config['order_update_user']) {
            $this->add(array(
                'name' => 'update_user',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // promo_value
        if ($config['order_promo']) {
            $this->add(array(
                'name' => 'promo_value',
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
                'validators'    => array(
                    new \Module\Order\Validator\Term,
                ),
            ));
        }
    }
}    	
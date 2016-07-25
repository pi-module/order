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
        // Get config
        $config = Pi::service('registry')->config->read('order', 'order');

        // Check for load register form
        $registerFiltersName = array();
        if (Pi::service('module')->isActive('user')
            && !Pi::service('authentication')->hasIdentity()
            && isset($_SESSION['session_order'])
            && !empty($_SESSION['session_order'])
        ) {
            $registerFilters = Pi::api('form', 'user')->loadFilters('register');
            foreach ($registerFilters as $filter) {
                $registerFiltersName[] = $filter['name'];
                $this->add($filter);
            }
        }
        // customer_id
        $this->add(array(
            'name' => 'customer_id',
            'required' => false,
        ));
        // name
        if ($config['order_name']) {
            // first_name
            if (!in_array('first_name', $registerFiltersName)) {
                $this->add(array(
                    'name' => 'first_name',
                    'required' => true,
                    'filters' => array(
                        array(
                            'name' => 'StringTrim',
                        ),
                    ),
                ));
            }
            // last_name
            if (!in_array('last_name', $registerFiltersName)) {
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
        }
        // id_number
        if ($config['order_idnumber'] && !in_array('id_number', $registerFiltersName)) {
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
        if ($config['order_email'] && !in_array('email', $registerFiltersName)) {
            $this->add(array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
                'validators' => array(
                    array(
                        'name' => 'EmailAddress',
                        'options' => array(
                            'useMxCheck' => false,
                            'useDeepMxCheck' => false,
                            'useDomainCheck' => false,
                        ),
                    ),
                    new UserEmailValidator(array(
                        'blacklist' => false,
                        'check_duplication' => false,
                    )),
                ),
            ));
        }
        // phone
        if ($config['order_phone'] && !in_array('phone', $registerFiltersName)) {
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
        if ($config['order_mobile'] && !in_array('mobile', $registerFiltersName)) {
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
        if ($config['order_company'] && !in_array('company', $registerFiltersName)) {
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
            if (!in_array('company_id', $registerFiltersName)) {
                $this->add(array(
                    'name' => 'company_id',
                    'required' => false,
                    'filters' => array(
                        array(
                            'name' => 'StringTrim',
                        ),
                    ),
                ));
            }
            // company_vat
            if (!in_array('company_vat', $registerFiltersName)) {
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
        }
        // address1
        if ($config['order_address1'] && !in_array('address1', $registerFiltersName)) {
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
        if ($config['order_address2'] && !in_array('address2', $registerFiltersName)) {
            $this->add(array(
                'name' => 'address2',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // country
        if ($config['order_country'] && !in_array('country', $registerFiltersName)) {
            $this->add(array(
                'name' => 'country',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // state
        if ($config['order_state'] && !in_array('state', $registerFiltersName)) {
            $this->add(array(
                'name' => 'state',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // city
        if ($config['order_city'] && !in_array('city', $registerFiltersName)) {
            $this->add(array(
                'name' => 'city',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // zip_code
        if ($config['order_zip'] && !in_array('zip_code', $registerFiltersName)) {
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
        if ($config['order_packing'] && !in_array('packing', $registerFiltersName)) {
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
        if ($config['order_update_user'] && empty($registerFiltersName)) {
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
        // order_term
        if ($config['order_term'] && !empty($config['order_termurl'])) {
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
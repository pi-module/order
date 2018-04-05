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

class OrderUpdateFilter extends InputFilter
{
    public function __construct($option = array())
    {
        if ($option['mode'] == 'add') {

            $this->add(array(
                'name' => 'uid',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
                'validators' => array(
                    new \Module\Order\Validator\User,
                ),
            ));
        
            // type_commodity
            $this->add(array(
                'name' => 'type_commodity',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            
            // module_name
            $this->add(array(
                'name' => 'module_name',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'product_type',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // module_item
            $this->add(array(
                'name' => 'module_item',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // module_item
            $this->add(array(
                'name' => 'product',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // time_create
            $this->add(array(
                'name' => 'time_create',
                'required' => true,
            ));
            
            $this->add(array(
                'name' => 'time_start',
                'required' => false,
            ));
            
            $this->add(array(
                'name' => 'time_end',
                'required' => false,
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
        }
        
        // name
        if ($option['config']['order_name']) {
            // first_name
            $this->add(array(
                'name' => 'delivery_first_name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // last_name
            $this->add(array(
                'name' => 'delivery_last_name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            
            $this->add(array(
                'name' => 'invoicing_first_name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // last_name
            $this->add(array(
                'name' => 'invoicing_last_name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }

        $this->add(array(
            'name' => 'default_gateway',
            'required' => true,
        ));
            
        // id_number
        if ($option['config']['order_idnumber']) {
            $this->add(array(
                'name' => 'delivery_id_number',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_id_number',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            
        }
        // email
        if ($option['config']['order_email']) {
            $this->add(array(
                'name' => 'delivery_email',
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
            $this->add(array(
                'name' => 'invoicing_email',
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
        if ($option['config']['order_phone']) {
            $this->add(array(
                'name' => 'delivery_phone',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_phone',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // mobile
        if ($option['config']['order_mobile']) {
            $this->add(array(
                'name' => 'delivery_mobile',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_mobile',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // company
        if ($option['config']['order_company']) {
            // company
            $this->add(array(
                'name' => 'delivery_company',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_company',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // company extra
        if ($option['config']['order_company_extra']) {
            // company_id
            $this->add(array(
                'name' => 'delivery_company_id',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // company_vat
            $this->add(array(
                'name' => 'delivery_company_vat',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            
            $this->add(array(
                'name' => 'invoicing_company_id',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            // company_vat
            $this->add(array(
                'name' => 'invoicing_company_vat',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // address
        if ($option['config']['order_address1']) {
            $this->add(array(
                'name' => 'delivery_address1',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_address1',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // address 2
        if ($option['config']['order_address2']) {
            $this->add(array(
                'name' => 'delivery_address2',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_address2',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // country
        if ($option['config']['order_country']) {
            $this->add(array(
                'name' => 'delivery_country',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_country',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // state
        if ($option['config']['order_state']) {
            $this->add(array(
                'name' => 'delivery_state',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_state',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // city
        if ($option['config']['order_city']) {
            $this->add(array(
                'name' => 'delivery_city',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_city',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // zip_code
        if ($option['config']['order_zip']) {
            $this->add(array(
                'name' => 'delivery_zip_code',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
            $this->add(array(
                'name' => 'invoicing_zip_code',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                    ),
                ),
            ));
        }
        // packing
        if ($option['config']['order_packing']) {
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

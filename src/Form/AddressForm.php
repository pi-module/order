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
use Pi\Form\Form as BaseForm;

class AddressForm extends BaseForm
{
    protected $_id;
    public function __construct($id = null, $option = array())
    {
        $this->option = $option;
        $this->config = Pi::service('registry')->config->read('order', 'order');
        $this->_id = $id;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new AddressFilter($this->option);
        }
        return $this->filter;
    }
    
    public function init()
    {
       
        // customer_id
        $this->add(array(
            'name' => 'customer_id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        // name
        if ($this->config['order_name']) {
            // first_name
            $this->add(array(
                'name' => 'first_name',
                'options' => array(
                    'label' => __('First name'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            
            // last_name
            $this->add(array(
                'name' => 'last_name',
                'options' => array(
                    'label' => __('Last name'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            
        }

        // id_number
        if ($this->config['order_idnumber']) {
            $this->add(array(
                'name' => 'id_number',
                'options' => array(
                    'label' => __('ID number'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }

        // email
        if ($this->config['order_email']) {
            $this->add(array(
                'name' => 'email',
                'options' => array(
                    'label' => __('Email'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // phone
        if ($this->config['order_phone']) {
            $this->add(array(
                'name' => 'phone',
                'options' => array(
                    'label' => __('Phone'),
                ),
                'attributes' => array(
                    'type' => 'tel',
                    'pattern'=> "^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$",
                    'description' => '',

                )
            ));
        }
        // mobile
        if ($this->config['order_mobile']) {
            $this->add(array(
                'name' => 'mobile',
                'options' => array(
                    'label' => __('Mobile'),
                ),
                'attributes' => array(
                   'type' => 'tel',
                   'title'=> __("example : +33123456789"),
                    'pattern'=> "^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$",
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // company
        if ($this->config['order_company']) {
            // company
            $this->add(array(
                'name' => 'company',
                'options' => array(
                    'label' => __('Company'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
        }
        // company extra
        if ($this->config['order_company_extra']) {
            // company_id
            $this->add(array(
                'name' => 'company_id',
                'options' => array(
                    'label' => __('Company id'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            
            // company_vat
            $this->add(array(
                'name' => 'company_vat',
                'options' => array(
                    'label' => __('Company vat'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
        }
        // address
        if ($this->config['order_address1']) {
            $this->add(array(
                'name' => 'address1',
                'options' => array(
                    'label' => __('Delivery address'),
                ),
                'attributes' => array(
                    'type' => 'textarea',
                    'rows' => '3',
                    'cols' => '40',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // address 2
        if ($this->config['order_address2']) {
            $this->add(array(
                'name' => 'address2',
                'options' => array(
                    'label' => __('Invoicing Address'),
                ),
                'attributes' => array(
                    'type' => 'textarea',
                    'rows' => '3',
                    'cols' => '40',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // zip_code
        if ($this->config['order_zip']) {
            $this->add(array(
                'name' => 'zip_code',
                'options' => array(
                    'label' => __('Zip code'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // city
        if ($this->config['order_city']) {
            $this->add(array(
                'name' => 'city',
                'options' => array(
                    'label' => __('City'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,

                )
            ));
        }
        // state
        if ($this->config['order_state']) {
            $this->add(array(
                'name' => 'state',
                'options' => array(
                    'label' => __('State'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // country
        if ($this->config['order_country']) {
            if (!empty($this->config['order_countrylist'])) {
                // Set list
                $countries = explode('|', $this->config['order_countrylist']);
                foreach ($countries as $country) {
                    $countryList[$country] = $country;
                }
                // Make form
                $this->add(array(
                    'name' => 'country',
                    'type' => 'select',
                    'options' => array(
                        'label' => __('Country'),
                        'value_options' => $countryList,
                        'required' => true,
                    ),
                ));
            } else {
                $this->add(array(
                    'name' => 'country',
                    'options' => array(
                        'label' => __('Country'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                        'required' => true,
                    )
                ));
            }
        }
        
        $this->add(array(
            'name' => 'submit_address',
            'type' => 'submit',
            'attributes' => array(
                'value' => $this->_id ? __('Edit') :  __('Add'),
                'class' => 'btn btn-success',
            )
        ));
    }
}
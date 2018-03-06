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

class OrderUpdateForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderUpdateFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // uid
        if ($this->option['mode'] == 'add') {
            $this->add(array(
                'name' => 'uid',
                'options' => array(
                    'label' => __('User ID'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            // module_name
            $this->add(array(
                'name' => 'module_name',
                'type' => 'select',
                'options' => array(
                    'label' => __('Join to module'),
                    'value_options' => array(
                        'order' => __('Order module'),
                        'shop' => __('Shop module'),
                        'guide' => __('Guide module'),
                    ),
                ),
            ));
            
            $this->add(array(
                'name' => 'product_type',
                'options' => array(
                    'label' => __('Product type'),
                ),
                'attributes' => array(
                    'type' => 'text',
                )
            ));
            // module_item
            $this->add(array(
                'name' => 'product',
                'options' => array(
                    'label' => __('Product id'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => __('Put service / product id and join order to module'),
                )
            ));
            // module_item
            $this->add(array(
                'name' => 'module_item',
                'options' => array(
                    'label' => __('Module item associated to the product'),
                ),
                'attributes' => array(
                    'type' => 'text',
                )
            ));
            // time_create
            $this->add(array(
                'name' => 'time_create',
                'type' => 'datepicker',
                'options' => array(
                    'label' => __('Order date'),
                    'datepicker' => array(
                        'format' => 'yyyy-mm-dd',
                    ),
                ),
                'attributes' => array(
                    'id' => 'time-create',
                    'required' => true,
                )
            ));
            // product_price
            $this->add(array(
                'name' => 'product_price',
                'options' => array(
                    'label' => __('Product price'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            // shipping_price
            $this->add(array(
                'name' => 'shipping_price',
                'options' => array(
                    'label' => __('Shipping price'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            // packing_price
            $this->add(array(
                'name' => 'packing_price',
                'options' => array(
                    'label' => __('Packing price'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            // setup_price
            $this->add(array(
                'name' => 'setup_price',
                'options' => array(
                    'label' => __('Setup price'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            // vat_price
            $this->add(array(
                'name' => 'vat_price',
                'options' => array(
                    'label' => __('Vat price'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            
            // type_commodity
            $this->add(array(
                'name' => 'type_commodity',
                'type' => 'select',
                'options' => array(
                    'label' => __('Type commodity'),
                    'value_options' => array(
                        'product' => __('Product'),
                        'service' => __('Service'),
                    ),
                ),
            ));
            
            $this->add(array(
                'name' => 'time_start',
                'type' => 'datepicker',
                'options' => array(
                    'label' => __('Time start'),
                    'datepicker' => array(
                        'format' => 'yyyy-mm-dd',
                    ),
                ),
                'attributes' => array(
                    'required' => false,
                )
            ));
            
            $this->add(array(
                'name' => 'time_end',
                'type' => 'datepicker',
                'options' => array(
                    'label' => __('Time end'),
                    'datepicker' => array(
                        'format' => 'yyyy-mm-dd',
                    ),
                ),
                'attributes' => array(
                    'required' => false,
                )
            ));
        
        }
        
        
        // name
        if ($this->option['config']['order_name']) {
            // first_name
            $this->add(array(
                'name' => 'delivery_first_name',
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
                'name' => 'delivery_last_name',
                'options' => array(
                    'label' => __('Last name'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            
            $this->add(array(
                'name' => 'invoicing_first_name',
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
                'name' => 'invoicing_last_name',
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
        if ($this->option['config']['order_idnumber']) {
            $this->add(array(
                'name' => 'delivery_id_number',
                'options' => array(
                    'label' => __('ID number'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            $this->add(array(
                'name' => 'invoicing_id_number',
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
        if ($this->option['config']['order_email']) {
            $this->add(array(
                'name' => 'delivery_email',
                'options' => array(
                    'label' => __('Email'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            $this->add(array(
                'name' => 'invoicing_email',
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
        if ($this->option['config']['order_phone']) {
            $this->add(array(
                'name' => 'delivery_phone',
                'options' => array(
                    'label' => __('Phone'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
            $this->add(array(
                'name' => 'invoicing_phone',
                'options' => array(
                    'label' => __('Phone'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
        }
        // mobile
        if ($this->option['config']['order_mobile']) {
            $this->add(array(
                'name' => 'delivery_mobile',
                'options' => array(
                    'label' => __('Mobile'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            $this->add(array(
                'name' => 'invoicing_mobile',
                'options' => array(
                    'label' => __('Mobile'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // company
        if ($this->option['config']['order_company']) {
            // company
            $this->add(array(
                'name' => 'delivery_company',
                'options' => array(
                    'label' => __('Company'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            $this->add(array(
                'name' => 'invoicing_company',
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
        if ($this->option['config']['order_company_extra']) {
            // company_id
            $this->add(array(
                'name' => 'delivery_company_id',
                'options' => array(
                    'label' => __('Company id'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            $this->add(array(
                'name' => 'invoicing_company_id',
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
                'name' => 'delivery_company_vat',
                'options' => array(
                    'label' => __('Company vat'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            $this->add(array(
                'name' => 'invoicing_company_vat',
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
        if ($this->option['config']['order_address1']) {
            $this->add(array(
                'name' => 'delivery_address1',
                'options' => array(
                    'label' => __('Address'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            $this->add(array(
                'name' => 'invoicing_address1',
                'options' => array(
                    'label' => __('Address'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
        }
        // address 2
        if ($this->option['config']['order_address2']) {
            $this->add(array(
                'name' => 'delivery_address2',
                'options' => array(
                    'label' => __('Address addition'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
            $this->add(array(
                'name' => 'invoicing_address2',
                'options' => array(
                    'label' => __('Address addition'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                )
            ));
        }
        // country
        if ($this->option['config']['order_country']) {
            if (!empty($this->option['config']['order_countrylist'])) {
                // Set list
                $countries = explode('|', $this->option['config']['order_countrylist']);
                foreach ($countries as $country) {
                    $countryList[$country] = $country;
                }
                // Make form
                $this->add(array(
                    'name' => 'delivery_country',
                    'type' => 'select',
                    'options' => array(
                        'label' => __('Country'),
                        'value_options' => $countryList,
                    ),
                ));
                $this->add(array(
                    'name' => 'invoicing_country',
                    'type' => 'select',
                    'options' => array(
                        'label' => __('Country'),
                        'value_options' => $countryList,
                    ),
                ));
            } else {
                $this->add(array(
                    'name' => 'delivery_country',
                    'options' => array(
                        'label' => __('Country'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                    )
                ));
                $this->add(array(
                    'name' => 'invoicing_country',
                    'options' => array(
                        'label' => __('Country'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'description' => '',
                    )
                ));
            }
        }
        // state
        if ($this->option['config']['order_state']) {
            $this->add(array(
                'name' => 'delivery_state',
                'options' => array(
                    'label' => __('State'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
             $this->add(array(
                'name' => 'invoicing_state',
                'options' => array(
                    'label' => __('State'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
            
        }
        // city
        if ($this->option['config']['order_city']) {
            $this->add(array(
                'name' => 'delivery_city',
                'options' => array(
                    'label' => __('City'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
            $this->add(array(
                'name' => 'invoicing_city',
                'options' => array(
                    'label' => __('City'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',

                )
            ));
        }
        // zip_code
        if ($this->option['config']['order_zip']) {
            $this->add(array(
                'name' => 'delivery_zip_code',
                'options' => array(
                    'label' => __('Zip code'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
                    'required' => true,
                )
            ));
            
            $this->add(array(
                'name' => 'invoicing_zip_code',
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
        // packing
        if ($this->option['config']['order_packing']) {
            $this->add(array(
                'name' => 'packing',
                'type' => 'checkbox',
                'options' => array(
                    'label' => __('Packing'),
                ),
                'attributes' => array(
                    'description' => '',
                )
            ));
        }
        
        // Save order
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Save'),
                'class' => 'btn btn-success',
            )
        ));
        
        $groups = array(
            'order' => array (
                "label" => __('Order'),
                "elements" => array('uid', 'status_order')
            ),
            'delivery_address' => array(
                'label' => __('Delivery address'),
                'elements' => array('delivery_first_name', 'delivery_last_name', 'delivery_email', 'delivery_mobile', 'delivery_address1', 'delivery_city', 'delivery_zip_code', 'delivery_country', 'delivery_address2', 'delivery_company', 'delivery_company_id', 'delivery_company_vat')
            ),
            'invoicing_address' => array(
                'label' => __('Invoicing address'),
                'elements' => array('invoicing_first_name', 'invoicing_last_name', 'invoicing_email', 'invoicing_mobile', 'invoicing_address1', 'invoicing_city', 'invoicing_zip_code', 'invoicing_country', 'invoicing_address2', 'invoicing_company', 'invoicing_company_id', 'invoicing_company_vat')
            ),
            'product' => array (
                "label" => __('Product'),
                "elements" => array('type_commodity', 'module_name', 'product_type', 'product', 'module_item', 'time_create', 'time_start', 'time_end', 'product_price', 'shipping_price', 'packing_price', 'setup_price', 'vat_price')
            ),
       );
       
       foreach ($groups as $key => &$group) {
            foreach ($group['elements'] as $key =>  &$field) {
                if (!array_key_exists($field, $this->getElements())) {
                    unset($group['elements'][$key]);
                }
            }   
            
       }
      foreach ($groups as $key => &$group) {
          if (count($group['elements']) == 0) {
                unset($groups[$key]);
          } 
       }
       $this->setGroups($groups);
    }
}
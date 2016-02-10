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

class OrderAddForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $this->option = $option;
        $this->config = Pi::service('registry')->config->read('order', 'order');
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderAddFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // uid
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
                    'type' => 'text',
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
                    'type' => 'text',
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
        if ($this->config['order_address']) {
            $this->add(array(
                'name' => 'address1',
                'options' => array(
                    'label' => __('Delivery address'),
                ),
                'attributes' => array(
                    'type' => 'text',
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
                    'label' => __('Address 2'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'description' => '',
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
                    )
                ));
            }
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
        // packing
        if ($this->config['order_packing']) {
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
        // Set gateway
        $gatewayList = Pi::api('gateway', 'order')->getActiveGatewayName();
        if (count($gatewayList) == 1) {
            $gatewayList = array_keys($gatewayList);
            // gateway
            $this->add(array(
                'name' => 'gateway',
                'attributes' => array(
                    'type' => 'hidden',
                    'value' => $gatewayList['0'],
                ),
            ));
        } else {
            // gateway
            $this->add(array(
                'name' => 'gateway',
                'type' => 'select',
                'options' => array(
                    'label' => __('Adapter'),
                    'value_options' => $gatewayList,
                ),
                'attributes' => array(
                    'id' => 'select-payment',
                    'size' => 1,
                    'required' => true,
                )
            ));
        }
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
        // Save order
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Save'),
                'class' => 'btn btn-success',
            )
        ));
    }
}
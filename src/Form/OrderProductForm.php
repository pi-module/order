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
use Pi\Form\Form as BaseForm;

class OrderProductForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderProductFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(
            [
                'name'       => 'module',
                'options'    => [
                    'label'         => __('Module name'),
                    'value_options' => [
                        'order' => 'order',
                        'shop'  => 'shop',
                        'guide' => 'guide',
                        'event' => 'event',
                        'video' => 'video',
                        'plans' => 'plans',
                    ],
                ],
                'type'       => 'select',
                'attributes' => [
                    'required' => true,
                ],
            ]
        );


        $this->add(
            [
                'name'       => 'product_type',
                'options'    => [
                    'label' => __('Product type'),
                ],
                'attributes' => [
                    'type' => 'text',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'product',
                'options'    => [
                    'label' => __('Product / service ID'),
                ],
                'attributes' => [
                    'type'     => 'text',
                    'required' => true,
                ],
            ]
        );

        // module_item
        $this->add(
            [
                'name'       => 'module_item',
                'options'    => [
                    'label' => __('Module item associated to the product'),
                ],
                'attributes' => [
                    'type' => 'text',
                ],
            ]
        );


        // product_price
        $this->add(
            [
                'name'       => 'product_price',
                'options'    => [
                    'label' => __('Price ext VAT'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // product_price
        $this->add(
            [
                'name'       => 'discount_price',
                'options'    => [
                    'label' => __('Discount'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );

        // shipping_price
        $this->add(
            [
                'name'       => 'shipping_price',
                'options'    => [
                    'label' => __('Shipping price'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // packing_price
        $this->add(
            [
                'name'       => 'packing_price',
                'options'    => [
                    'label' => __('Packing price'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // setup_price
        $this->add(
            [
                'name'       => 'setup_price',
                'options'    => [
                    'label' => __('Setup price'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );
        // vat_price
        $this->add(
            [
                'name'       => 'vat_price',
                'options'    => [
                    'label' => __('Vat price'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'time_start',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Time start'),
                    'datepicker' => [
                        'format'         => 'yyyy-mm-dd',
                        'autoclose'      => true,
                        'todayBtn'       => true,
                        'todayHighlight' => true,
                        'weekStart'      => 1,
                    ],
                ],
                'attributes' => [
                    'required' => false,
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'time_end',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Time end'),
                    'datepicker' => [
                        'format'         => 'yyyy-mm-dd',
                        'autoclose'      => true,
                        'todayBtn'       => true,
                        'todayHighlight' => true,
                        'weekStart'      => 1,
                    ],
                ],
                'attributes' => [
                    'required' => false,
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'admin_note',
                'options'    => [
                    'label' => __('Admin note'),
                ],
                'attributes' => [
                    'type'        => 'textarea',
                    'description' => '',
                ],
            ]
        );

        $elemsForGroup = [];
        foreach (['order', 'shop', 'guide', 'event', 'video', 'plans'] as $module) {
            if (Pi::service('module')->isActive($module)) {
                $elems = Pi::api('order', $module)->getExtraFieldsFormForOrder();
                foreach ($elems as $elem) {
                    $this->add($elem);
                    $elemsForGroup[] = $elem['name'];
                }
            }
        }
        // Save order
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Save'),
                    'class' => 'btn btn-success',
                ],
            ]
        );
    }
}

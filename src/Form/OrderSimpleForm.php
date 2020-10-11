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

class OrderSimpleForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->option = $option;
        $this->config = Pi::service('registry')->config->read('order');
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new OrderSimpleFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(
            [
                'name'       => 'address_delivery_id',
                'attributes' => [
                    'type'  => 'hidden',
                    'value' => $this->option['delivery_address'],
                ],
            ]
        );
        $this->add(
            [
                'name'       => 'address_invoicing_id',
                'attributes' => [
                    'type'  => 'hidden',
                    'value' => $this->option['invoicing_address'],
                ],
            ]
        );

        // packing
        if ($this->config['order_packing']) {
            $this->add(
                [
                    'name'       => 'packing',
                    'type'       => 'checkbox',
                    'options'    => [
                        'label' => __('Packing'),
                    ],
                    'attributes' => [
                        'description' => '',
                    ],
                ]
            );
        }
        // Check type_commodity
        $gatewayList = Pi::api('gateway', 'order')->getActiveGatewayName();
        switch ($this->option['type_commodity']) {
            case 'product':
                if ($this->config['order_location_delivery']) {
                    // location
                    $this->add(
                        [
                            'name'       => 'location',
                            'options'    => [
                                'label' => __('Location'),
                            ],
                            'attributes' => [
                                'type'     => 'hidden',
                                'required' => true,
                            ],
                        ]
                    );
                    // delivery
                    $this->add(
                        [
                            'name'       => 'delivery',
                            'type'       => 'Module\Order\Form\Element\Delivery',
                            'options'    => [
                                'label'         => __('Delivery methods'),
                                'value_options' => [],
                                'location'      => $this->option['location'],
                            ],
                            'attributes' => [
                                'id'       => 'address-select-delivery',
                                'size'     => 3,
                                'required' => true,
                            ],
                        ]
                    );
                    // check gateway
                    if (count($gatewayList) == 1) {
                        $gatewayList = array_keys($gatewayList);
                        // gateway
                        $this->add(
                            [
                                'name'       => 'default_gateway',
                                'attributes' => [
                                    'id'    => 'address-select-payment',
                                    'type'  => 'hidden',
                                    'value' => $gatewayList['0'],
                                ],
                            ]
                        );
                    } else {
                        // gateway
                        $this->add(
                            [
                                'name'       => 'default_gateway',
                                'type'       => 'radio',
                                'options'    => [
                                    'label'         => __('Choose your payment method'),
                                    'value_options' => [],
                                ],
                                'attributes' => [
                                    'id'       => 'address-select-payment',
                                    'required' => true,
                                ],
                            ]
                        );
                    }
                } else {
                    if (count($gatewayList) == 1) {
                        $gatewayList = array_keys($gatewayList);
                        // gateway
                        $this->add(
                            [
                                'name'       => 'default_gateway',
                                'attributes' => [
                                    'id'    => 'address-select-payment',
                                    'type'  => 'hidden',
                                    'value' => $gatewayList['0'],
                                ],
                            ]
                        );
                    } else {
                        // gateway
                        $this->add(
                            [
                                'name'       => 'default_gateway',
                                'type'       => 'radio',
                                'options'    => [
                                    'label'         => __('Choose your payment method'),
                                    'value_options' => $gatewayList,
                                ],
                                'attributes' => [
                                    'id'       => 'address-select-payment',
                                    'required' => true,
                                ],
                            ]
                        );
                    }
                }
                break;

            case 'booking':
            case 'service':
                if (count($gatewayList) == 1) {
                    $gatewayList = array_keys($gatewayList);
                    // gateway
                    $this->add(
                        [
                            'name'       => 'default_gateway',
                            'attributes' => [
                                'type'  => 'hidden',
                                'value' => $gatewayList['0'],
                            ],
                        ]
                    );
                } else {
                    // gateway
                    $this->add(
                        [
                            'name'       => 'default_gateway',
                            'type'       => 'radio',
                            'options'    => [
                                'label'         => __('Choose your payment method'),
                                'value_options' => $gatewayList,
                            ],
                            'attributes' => [
                                'id'       => 'address-select-payment',
                                'required' => true,
                            ],
                        ]
                    );
                }
                break;
        }
        // user_note
        if ($this->config['order_usernote']) {
            $this->add(
                [
                    'name'       => 'user_note',
                    'options'    => [
                        'label' => __('User note'),
                    ],
                    'attributes' => [
                        'type'        => 'textarea',
                        'rows'        => '5',
                        'cols'        => '40',
                        'placeholder' => __('Give here more details you think we need to know about'),
                    ],
                ]
            );
        }

        if (!$this->option['pay_all'] && count($this->option['composition']) > 1) {
            $this->add(
                [
                    'name' => 'html1',
                    'type' => 'html-raw',

                    'attributes' => [
                        'value' => '<div class="mt-2 mb-3 p-2 border-success border">' . sprintf(
                                __('You chose to pay through 2 installments : %s now, and %s before %s'),
                                _currency(number_format($this->option['due_price'] * $this->option['composition'][0] / 100, 2, '.', '')), _currency(
                                number_format(
                                    $this->option['due_price'] - number_format($this->option['due_price'] * $this->option['composition'][0] / 100, 2, '.', ''),
                                    2, '.', ''
                                )
                            ), _date($this->option['limit_date'])
                            ) . '</div>',
                    ],
                ]
            );
        }

        // order_term
        if ($this->config['order_term'] && !empty($this->config['order_termurl'])) {
            $term = sprintf('<a href="%s" target="_blank">%s</a>', $this->config['order_termurl'], __('Terms & Conditions'));
            $term = sprintf(__('Accept the %s'), $term);
            $this->add(
                [
                    'name'       => 'order_term',
                    'type'       => 'checkbox',
                    'options'    => [
                        'label' => '',
                    ],
                    'attributes' => [
                        'description' => $term,
                        'required'    => true,
                    ],
                ]
            );
        }
        // Save
        if ($this->config['order_payment'] == 'payment') {
            $title = sprintf(__('Pay %s'), _currency(number_format($this->option['due_price'] * $this->option['composition'][0] / 100, 2, '.', '')));
        } else {
            $title = __('Save order');
        }
        $class = 'btn btn-success submit_order_simple';
        if (!$this->option['delivery_address'] && !$this->option['invoicing_address']) {
            $class .= ' fortooltip disabled';
        }

        $this->add(
            [
                'name'       => 'submit_order_simple',
                'type'       => 'submit',
                'attributes' => [
                    'value' => $title,
                    'class' => $class,
                    'title' => __('You need to create an address before pay'),
                ],
            ]
        );
    }
}

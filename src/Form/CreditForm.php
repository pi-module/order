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

class CreditForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        $this->option = $option;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new CreditFilter($this->option);
        }
        return $this->filter;
    }

    public function init()
    {
        // uid
        $this->add(
            [
                'name'       => 'uid',
                'options'    => [
                    'label' => __('User ID'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                    'required'    => true,
                ],
            ]
        );
        // amount
        $this->add(
            [
                'name'       => 'amount',
                'options'    => [
                    'label' => __('Amount'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                    'required'    => true,
                ],
            ]
        );
        // status_fluctuation
        $this->add(
            [
                'name'       => 'status_fluctuation',
                'type'       => 'select',
                'options'    => [
                    'label'         => __('Type fluctuation'),
                    'value_options' => [
                        'increase' => __('Increase'),
                        'decrease' => __('Decrease'),
                    ],
                ],
                'attributes' => [
                    'required' => true,
                ],
            ]
        );
        // Set module
        $valueOptions = [];
        $moduleList   = Pi::registry('modulelist')->read();
        if ($this->option['type'] == 'module') {
            if (isset($moduleList['shop'])) {
                $valueOptions['shop'] = $moduleList['shop']['title'];
            }
            if (isset($moduleList['event'])) {
                $valueOptions['event'] = $moduleList['event']['title'];
            }
            if (isset($moduleList['guide'])) {
                $valueOptions['guide'] = $moduleList['guide']['title'];
            }
            if (isset($moduleList['video'])) {
                $valueOptions['video'] = $moduleList['video']['title'];
            }
            if (isset($moduleList['plans'])) {
                $valueOptions['plans'] = $moduleList['plans']['title'];
            }
        } else {
            $valueOptions['system'] = $moduleList['system']['title'];
        }
        $this->add(
            [
                'name'       => 'module',
                'type'       => 'select',
                'options'    => [
                    'label'         => __('Select module for use this credit'),
                    'value_options' => $valueOptions,
                ],
                'attributes' => [
                    'required' => true,
                ],
            ]
        );
        // message_user
        $this->add(
            [
                'name'       => 'message_user',
                'options'    => [
                    'label' => __('User message'),
                ],
                'attributes' => [
                    'type'     => 'textarea',
                    'rows'     => '5',
                    'cols'     => '40',
                    'required' => true,
                ],
            ]
        );
        // message_admin
        $this->add(
            [
                'name'       => 'message_admin',
                'options'    => [
                    'label' => __('Admin message'),
                ],
                'attributes' => [
                    'type'     => 'textarea',
                    'rows'     => '5',
                    'cols'     => '40',
                    'required' => true,
                ],
            ]
        );
        // Save
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Save'),
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}

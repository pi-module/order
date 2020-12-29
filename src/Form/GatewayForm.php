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

class GatewayForm extends BaseForm
{
    public function __construct($name = null, $field)
    {
        $this->field = $field;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new GatewayFilter(null);
        }
        return $this->filter;
    }

    public function init()
    {
        // Set extra field
        if (!empty($this->field)) {
            foreach ($this->field as $field) {
                $attributes = isset($field['attributes']) ? $field['attributes'] : [];
                $options    = isset($field['options']) ? $field['options'] : [];

                if ($field['type'] == 'hidden') {
                    $attributes['type'] = 'hidden';
                    $this->add(
                        [
                            'name'       => $field['name'],
                            'attributes' => $attributes,
                            'options'    => $options,
                        ]
                    );
                } elseif ($field['type'] == 'checkbox') {
                    $options['label'] = $field['label'];
                    $this->add(
                        [
                            'name'    => $field['name'],
                            'type'    => 'checkbox',
                            'options' => $options,
                        ]
                    );
                } else {
                    $options['label']   = $field['label'];
                    $attributes['type'] = $field['type'];
                    $this->add(
                        [
                            'name'       => $field['name'],
                            'options'    => $options,
                            'attributes' => $attributes,
                        ]
                    );
                }
            }
        }
        // Save
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Submit'),
                ],
            ]
        );
    }
}

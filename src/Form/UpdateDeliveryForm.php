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

class UpdateDeliveryForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new UpdateDeliveryFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // status_delivery
        $this->add(
            [
                'name'    => 'status_delivery',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Delivery'),
                    'value_options' => [
                        1 => __('Not processed'),
                        2 => __('Packed'),
                        3 => __('Posted'),
                        4 => __('Delivered'),
                        5 => __('Back eaten'),
                    ],
                ],
            ]
        );
        // Save
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Update'),
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}

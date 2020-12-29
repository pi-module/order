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

class UpdateOrderStatusForm extends BaseForm
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new UpdateOrderStatusFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // status_order
        $this->add(
            [
                'name'    => 'status_order',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Order'),
                    'value_options' => \Module\Order\Model\Order::getStatusList($this->option['has_valid_invoice']),

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

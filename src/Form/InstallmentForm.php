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

class InstallmentForm extends BaseForm
{
    protected $_options;

    public function __construct($name = null, $options = [])
    {
        $this->_options = $options;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new InstallmentFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(
            [
                'name'       => 'gateway',
                'type'       => 'select',
                'options'    => [
                    'label'         => __('Gateway'),
                    'value_options' => Pi::api('gateway', 'order')->getAdminGatewayList(),
                    'required'      => true,
                ],
                'attributes' => [
                    'disabled' => $this->_options['readonly'],
                ],
            ]
        );
        $this->add(
            [
                'name'       => 'status_payment',
                'type'       => 'select',
                'options'    => [
                    'label'         => __('Status'),
                    'value_options' => \Module\Order\Model\Invoice\Installment::getStatusList(),
                    'required'      => true,
                ],
                'attributes' => [
                    'disabled' => $this->_options['readonly'],
                ],
            ]
        );
        $this->add(
            [
                'name'       => 'time_duedate',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Due date'),
                    'datepicker' => [
                        'format'         => 'yyyy-mm-dd',
                        'autoclose'      => true,
                        'todayBtn'       => true,
                        'todayHighlight' => true,
                        'weekStart'      => 1,
                    ],
                ],
                'attributes' => [
                    'disabled' => $this->_options['readonly'],
                ],
            ]
        );
        $this->add(
            [
                'name'       => 'time_payment',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Payment date'),
                    'datepicker' => [
                        'format' => 'yyyy-mm-dd',
                    ],
                ],
                'attributes' => [
                    'id'       => 'time-create',
                    'disabled' => $this->_options['readonly'],

                ],
            ]
        );

        $this->add(
            [
                'name'       => 'comment',
                'options'    => [
                    'label' => __('Comment'),
                ],
                'attributes' => [
                    'type' => 'textarea',
                    'rows' => '5',
                    'cols' => '40',
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

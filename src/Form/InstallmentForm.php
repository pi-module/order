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
    public function __construct($name = null, $options = array())
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
        $this->add(array(
            'name' => 'gateway',
            'type' => 'select',
            'options' => array(
                'label' => __('Gateway'),
                'value_options' => Pi::api('gateway', 'order')->getAdminGatewayList(),
                'required' => true,
            ),
            'attributes' => array(
                'disabled' => $this->_options['readonly']
            )
        ));
        $this->add(array(
            'name' => 'status_payment',
            'type' => 'select',
            'options' => array(
                'label' => __('Status'),
                'value_options' => \Module\Order\Model\Invoice\Installment::getStatusList(),
                'required' => true,
            ),
            'attributes' => array(
                'disabled' => $this->_options['readonly']
            )
        ));
        $this->add(array(
            'name' => 'time_duedate',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Due date'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                    'autoclose' => true,
                    'todayBtn' => true,
                    'todayHighlight' => true,
                    'weekStart' => 1,
                ),
            ),
            'attributes' => array(
                'required' => false,
            )
        ));
        $this->add(array(
            'name' => 'time_payment',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Payment date'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            ),
            'attributes' => array(
                'id' => 'time-create',
                'required' => false,
            )
        ));
        
        $this->add(array(
            'name' => 'comment',
            'options' => array(
                'label' => __('Comment'),
            ),
            'attributes' => array(
                'type' => 'textarea',
                'rows' => '5',
                'cols' => '40',
            )
        ));
        
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Save'),
                'class' => 'btn btn-primary',
            )
        ));
    }
}
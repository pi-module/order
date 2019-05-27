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

class InvoiceForm extends BaseForm
{
    protected $_installments;
    public function __construct($name = null, $installments = array())
    {
        $this->_installments = $installments;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new InvoiceFilter;
        }
        return $this->filter;
    }

    public function init()
    {  
        $this->add(array(
            'name' => 'time_invoice',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Invoice date'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                    'autoclose' => true,
                    'todayBtn' => true,
                    'todayHighlight' => true,
                    'weekStart' => 1,
                ),
            ),
            'attributes' => array(
                'required' => true,
            )
        ));
        
        $this->add(array(
            'name' => 'type_payment',
            'type' => 'select',
            'options' => array(
                'label' => __('Type payment'),
                'value_options' => array('onetime' => 'onetime') + $this->_installments
            ),
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

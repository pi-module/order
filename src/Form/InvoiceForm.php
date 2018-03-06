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
            'name' => 'installments',
            'type' => 'select',
            'options' => array(
                'label' => __('Installment'),
                'value_options' => array(0 => 'onetime') + $this->_installments
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

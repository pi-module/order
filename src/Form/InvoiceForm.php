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

    public function __construct($name = null, $installments = [])
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
        $this->add(
            [
                'name'       => 'time_invoice',
                'type'       => 'datepicker',
                'options'    => [
                    'label'      => __('Invoice date'),
                    'datepicker' => [
                        'format'         => 'yyyy-mm-dd',
                        'autoclose'      => true,
                        'todayBtn'       => true,
                        'todayHighlight' => true,
                        'weekStart'      => 1,
                    ],
                ],
                'attributes' => [
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'name'    => 'type_payment',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Type payment'),
                    'value_options' => ['onetime' => 'onetime'] + $this->_installments,
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

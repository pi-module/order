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

class UpdateInvoiceForm extends BaseForm
{
    protected $_options = [];

    public function __construct($name = null, $options = [])
    {
        $this->_options = $options;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new UpdateInvoiceFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // status_payment
        $this->add(
            [
                'name'    => 'status',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Status'),
                    'value_options' => \Module\Order\Model\Invoice::getStatusList($this->_options['status']),
                ],
            ]
        );
        // gateway
        /* $this->add(array(
            'name' => 'gateway',
            'type' => 'Module\Order\Form\Element\Gateway',
            'options' => array(
                'label' => __('Adapter'),
                'gateway' => '',
            ),
        )); */
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

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

class PayForm extends BaseForm
{
    public function __construct($name = null, $field)
    {
        $this->field = $field;
        parent::__construct($name);
    }

    /* public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PayFilter($this->field);
        }
        return $this->filter;
    } */

    public function init()
    {
        $this->setAttribute('enctype','multipart/form-data');

        // Set extra field
        if (!empty($this->field)) {
            foreach ($this->field as $field) {
                if ($field['type'] != 'hidden') {
                    $this->add(array(
                        'name' => $field['name'],
                        'options' => array(
                            'label' => $field['label'],
                        ),
                        'attributes' => array(
                            'type' => $field['type'],
                        )
                    ));
                } else {
                    $this->add(array(
                        'name' => $field['name'],
                        'attributes' => array(
                            'type' => 'hidden',
                        ),
                    ));
                }
            }
        }
        // Save
        /* $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Pay'),
            )
        )); */
    }
}
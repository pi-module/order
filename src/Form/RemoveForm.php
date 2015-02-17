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

class RemoveForm extends BaseForm
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function init()
    {
        // id
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        // Attention
        $this->add(array(
            'name' => 'attention',
            'options' => array(
                'label' => __('Attention'),
            ),
            'attributes' => array(
                'type' => 'description',
                'description' => __('You have another open payment process, if have have another page for online payment, please fish for courent payment and after that start new one. But is you cancel your old payment or it have any other problem and you want close all old proess and start new one, Please conferm this meaasage and select other payment'),
            )
        ));
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
            	'class' => 'btn btn-danger',
                'value' => __('Conferm'),
            )
        ));
    }
}	
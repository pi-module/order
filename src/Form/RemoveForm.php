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
                'description' => __('You have stopped the payment process. You will be redirected to the Order Checkout page so you can relaunch payment / checkout process.<br> Or maybe you try to launch several payment at the same time :  you cannot proceed to si;ultaneous payments, please proceed one by one. You will be also redirected to Order Checkout page'),
            )
        ));
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'class' => 'btn btn-danger',
                'value' => __('I understand'),
            )
        ));
    }
}	
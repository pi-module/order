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

class PromoCheckoutForm extends BaseForm
{
    
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PromoCheckoutFilter($this->option);
        }
        return $this->filter;
    }
    
    public function init()
    {
         $this->add(array(
            'name' => 'code',
            'options' => array(
                'label' => __('Your promo code'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
                'class' => 'form-control'
            )
        ));
        
        $this->add(array(
            'name' => 'submit_promo',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Apply'),
                'class' => 'btn btn-refresh form-control',
            )
        ));
    }
}
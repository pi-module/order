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
        $this->add(
            [
                'name'       => 'code',
                'options'    => [
                    'label' => __('Your promo code'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                    'class'       => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'submit_promo',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Apply'),
                    'class' => 'btn btn-refresh form-control',
                ],
            ]
        );
    }
}
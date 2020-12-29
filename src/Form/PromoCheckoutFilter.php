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
use Laminas\InputFilter\InputFilter;

class PromoCheckoutFilter extends InputFilter
{
    public function __construct($option = [])
    {
        $this->add(
            [
                'name'     => 'code',
                'required' => true,
            ]
        );

        $this->add(
            [
                'name'     => 'submit_promo',
                'required' => false,
            ]
        );
    }
}

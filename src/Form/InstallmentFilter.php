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
use Zend\InputFilter\InputFilter;

class InstallmentFilter extends InputFilter
{
    public function __construct($options = [])
    {
        $this->add(
            [
                'name'     => 'gateway',
                'required' => isset($options['readonly']) && $options['readonly'] ? false : true,

            ]
        );

        $this->add(
            [
                'name'     => 'status_payment',
                'required' => isset($options['readonly']) && $options['readonly'] ? false : true,

            ]
        );
        $this->add(
            [
                'name'     => 'time_duedate',
                'required' => false,

            ]
        );
        $this->add(
            [
                'name'     => 'time_payment',
                'required' => false,
            ]
        );

        $this->add(
            [
                'name'     => 'comment',
                'required' => false,
            ]
        );

    }
}
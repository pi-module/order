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

class CreditSettingFilter extends InputFilter
{
    public function __construct($option = [])
    {
        // uid
        $this->add(
            [
                'name'     => 'uid',
                'required' => false,
            ]
        );
        // first_name
        $this->add(
            [
                'name'     => 'first_name',
                'required' => false,
            ]
        );
        // last_name
        $this->add(
            [
                'name'     => 'last_name',
                'required' => false,
            ]
        );
        // email
        $this->add(
            [
                'name'     => 'email',
                'required' => false,
            ]
        );
        // company
        $this->add(
            [
                'name'     => 'company',
                'required' => false,
            ]
        );
    }
}

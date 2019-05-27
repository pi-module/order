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

class CreditSettingFilter extends InputFilter
{
    public function __construct($option = array())
    {
        // uid
        $this->add(array(
            'name' => 'uid',
            'required' => false,
        ));
        // first_name
        $this->add(array(
            'name' => 'first_name',
            'required' => false,
        ));
        // last_name
        $this->add(array(
            'name' => 'last_name',
            'required' => false,
        ));
        // email
        $this->add(array(
            'name' => 'email',
            'required' => false,
        ));
        // company
        $this->add(array(
            'name' => 'company',
            'required' => false,
        ));
    }
}
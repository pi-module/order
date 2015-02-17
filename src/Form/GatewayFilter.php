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
use Zend\InputFilter\InputFilter;

class GatewayFilter extends InputFilter
{
    public function __construct($field)
    {
        if (!empty($field)) {
            foreach ($field as $item) {
                $this->add(array(
                    'name' => $item['name'],
                    'required' => $item['required'],
                ));
            }
        }
    }
}    	
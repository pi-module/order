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

class UpdateOrderStatusFilter extends InputFilter
{
    public function __construct()
    {
        // status_order
        $this->add(
            [
                'name'     => 'status_order',
                'required' => true,
            ]
        );
    }
}

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

namespace Module\Order\Form\Element;

use Pi;
use Laminas\Form\Element\Select;

class Gateway extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        $this->valueOptions = Pi::api('gateway', 'order')->getActiveGatewayName();
        if (empty($this->valueOptions)) {
            $this->valueOptions['offline'] = __('Offline');
        }
        return $this->valueOptions;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $this->Attributes = [
            'size'     => 5,
            'multiple' => 0,
            'class'    => 'form-control',
        ];
        // check form size
        if (isset($this->attributes['size'])) {
            $this->Attributes['size'] = $this->attributes['size'];
        }
        // check form multiple
        if (isset($this->attributes['multiple'])) {
            $this->Attributes['multiple'] = $this->attributes['multiple'];
        }
        return $this->Attributes;
    }
}

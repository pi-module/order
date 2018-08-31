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

class PromocodeFilter extends InputFilter
{
    public function __construct($option = array())
    {
        $this->add(array(
            'name' => 'id',
            'required' => false,
        ));        
        $this->add(array(
            'name' => 'code',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'Db/NoRecordExists',
                    'options' => array(
                        'table' => Pi::model('promocode', 'order')->getTable(),
                        'field' => 'code',
                        'adapter' => Pi::model('promocode', 'order')->getAdapter(),
                        'exclude' => array(
                            'field' => 'id',
                            'value' => isset($option['id']) ? $option['id'] : null
                        )                            
                    )
                )
            ),
        ));
        
        $this->add(array(
            'name' => 'promo',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'IsInt'
                ),
                array(
                    'name' => 'Between',
                    'options' => array('min' => 0, 'max' => 100)
                )
            ),
        ));
        
        $this->add(array(
            'name' => 'time_start',
            'required' => true,
        ));
        
        $this->add(array(
            'name' => 'time_end',
            'required' => true,
        ));
        
        $this->add(array(
            'name' => 'module',
            'required' => true,
        ));
        
        $this->add(array(
            'name' => 'showcode',
            'required' => true,
        ));
        
    }
}	
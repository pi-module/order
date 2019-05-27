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

class PromocodeForm extends BaseForm
{
    protected $_id;
    public function __construct($id = null, $modules = array(), $option = array())
    {
        $this->option = $option;
        $this->_id = $id;
        $this->_modules = $modules;
        parent::__construct();
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PromocodeFilter($this->option);
        }
        return $this->filter;
    }
    
    public function init()
    {
       
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->_id
            ),
        ));

        $this->add(array(
            'name' => 'code',
            'options' => array(
                'label' => __('Code'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        
        $this->add(array(
            'name' => 'promo',
            'options' => array(
                'label' => __('Promo (%)'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => '',
            )
        ));
        
         // time_start
        $this->add(array(
            'name' => 'datetimepicker_start',
            'type' => 'text',
            'options' => array(
                'label' => __('Start from'),
              
            ),
            'attributes' => array(
                'class' => 'datetimepicker-input',
                'required' => true,
                'id' => 'datetimepicker_start',
                'data-toggle'=>"datetimepicker",
                'data-target'=>"#datetimepicker_start"
            )
        ));
        
         $this->add(array(
            'name' => 'time_start',
            'type' => 'hidden',
            'attributes' => array(
                'required' => true,
            )
        ));
         $this->add(array(
            'name' => 'datetimepicker_end',
            'type' => 'text',
            'options' => array(
                'label' => __('End to'),
              
            ),
            'attributes' => array(
                'class' => 'datetimepicker-input',
                'required' => true,
                'id' => 'datetimepicker_end',
                'data-toggle'=>"datetimepicker",
                'data-target'=>"#datetimepicker_end"
            )
        ));
        
         $this->add(array(
            'name' => 'time_end',
            'type' => 'hidden',
            'attributes' => array(
                'required' => true,
            )
        ));
        
        

        $this->add(array(
            'name' => 'module',
            'type' => 'select',
            'options' => array(
                'label' => __('Module'),
                'value_options' => $this->_modules,
            ),
            'attributes' => array(
                'size' => 5,
                'multiple' => 1
            )
        ));
        $this->add(array(
            'name' => 'showcode',
            'type' => 'checkbox',
            'options' => array(
                'label' => __('Show Promo Code on front CTA'),
            ),
            
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => $this->_id ? __('Edit') :  __('Add'),
                'class' => 'btn btn-success',
            )
        ));
    }
}
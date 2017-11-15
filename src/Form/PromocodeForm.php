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
       
        // customer_id
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
        
         $this->add(array(
            'name' => 'time_start',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Start from'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            )
        ));
        
        // end
        $this->add(array(
            'name' => 'time_end',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('End to'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                ),
            )
        ));

        $this->add(array(
            'name' => 'module',
            'type' => 'select',
            'options' => array(
                'label' => __('Module'),
                'value_options' => $this->_modules,
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
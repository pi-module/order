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

class UpdateNoteForm extends BaseForm
{
    public function __construct($name = null, $option = [])
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new UpdateNoteFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // admin_note
        $this->add(
            [
                'name'       => 'admin_note',
                'options'    => [
                    'label' => __('Note'),
                ],
                'attributes' => [
                    'type'        => 'textarea',
                    'rows'        => '5',
                    'cols'        => '40',
                    'description' => '',
                    'required'    => true,
                ],
            ]
        );
        // Save
        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Update'),
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}

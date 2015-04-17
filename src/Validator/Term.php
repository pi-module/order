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

namespace Module\Order\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class Term extends AbstractValidator
{
    const TAKEN        = 'termExists';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::TAKEN     => 'You need accept our Accept Terms & Conditions for checkout',
    );

    protected $options = array();

    /**
     * Slug validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $value = intval($value);
        if ($value > 0) {
        	return true;
        } else {
        	$this->error(static::TAKEN);
        	return false;
        }
    }
}
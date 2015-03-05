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
namespace Module\Order\Block;

use Pi;

class Block
{
    public static function detail($options = array(), $module = null)
    {
        // Set options
        $block = array();
        $block = array_merge($block, $options);

        return $block;
    }
}
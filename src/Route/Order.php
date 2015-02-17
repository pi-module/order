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

namespace Module\Order\Route;

use Pi\Mvc\Router\Http\Standard;

class Order extends Standard
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'order',
        'controller'    => 'index',
        'action'        => 'index'
    );

    protected $actionList = array(
        'invoice', 'pay', 'result', 'notify', 'remove', 'cancel', 'finish', 'error'
    );

    /**
     * {@inheritDoc}
     */
    protected $structureDelimiter = '/';

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = array();
        $parts = array_filter(explode($this->structureDelimiter, $path));
        // Set matches
        $matches = array_merge($this->defaults, $matches);
        if (isset($parts[0]) && in_array($parts[0], $this->actionList)) {
            $matches['action'] = $this->decode($parts[0]);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $matches['id'] = intval($parts[1]);
            }
        }
        // return
        return $matches;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return string
     */
    public function assemble(
        array $params = array(),
        array $options = array()
    ) {
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }
        
        // Set module
        if (!empty($mergedParams['module'])) {
            $url['module'] = $mergedParams['module'];
        }

        // Set action
        if (!empty($mergedParams['action']) 
                && $mergedParams['action'] != 'index') 
        {
            $url['action'] = $mergedParams['action'];
        }
        
        // Set id
        if (!empty($mergedParams['id'])) {
            $url['id'] = $mergedParams['id'];
        }

        // Make url
        $url = implode($this->paramDelimiter, $url);

        if (empty($url)) {
            return $this->prefix;
        }
        return $this->paramDelimiter . $url;
    }
}

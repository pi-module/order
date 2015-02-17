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

namespace Module\Order\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController; 
use Pi\Paginator\Paginator;
use Zend\Json\Json;

class LogsController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $module = $this->params('module');
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $select = $this->getModel('log')->select()->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('log')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['value'] = Json::decode($list[$row->id]['value'], true);
            $list[$row->id]['user'] = Pi::user()->get($list[$row->id]['uid'], array('id', 'identity', 'name', 'email'));
            $list[$row->id]['time_create_view'] = _date($list[$row->id]['time_create']);
        }
        // Set paginator
        $count = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = $this->getModel('log')->select()->columns($count);
        $count = $this->getModel('log')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'        => $this->getModule(),
                'controller'    => 'log',
                'action'        => 'index',
            )),
        ));
        // Set view
        $this->view()->setTemplate('log_index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }	
}
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

namespace Module\Order\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Json\Json;

class LogsController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page   = $this->params('page', 1);
        $module = $this->params('module');
        // Get info
        $list   = [];
        $order  = ['id DESC', 'time_create DESC'];
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit  = intval($this->config('admin_perpage'));
        $select = $this->getModel('log')->select()->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('log')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id]                     = $row->toArray();
            $list[$row->id]['value']            = Json::decode($list[$row->id]['value'], true);
            $list[$row->id]['user']             = Pi::user()->get($row->uid, ['id', 'identity', 'name', 'email']);
            $list[$row->id]['time_create_view'] = _date($list[$row->id]['time_create']) . ' ' . date('H:i', $list[$row->id]['time_create']);
            $list[$row->id]['user_url']         = Pi::url(
                $this->url(
                    '',
                    [
                        'module'     => 'user',
                        'controller' => 'edit',
                        'action'     => 'index',
                        'uid'        => $row->uid,
                    ]
                )
            );
            $list[$row->id]['order_url']        = Pi::url(
                $this->url(
                    '',
                    [
                        'module'     => 'order',
                        'controller' => 'order',
                        'action'     => 'view',
                        'id'         => $row->order,
                    ]
                )
            );
        }
        // Set paginator
        $count     = ['count' => new Expression('count(*)')];
        $select    = $this->getModel('log')->select()->columns($count);
        $count     = $this->getModel('log')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(
            [
                'router' => $this->getEvent()->getRouter(),
                'route'  => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
                'params' => array_filter(
                    [
                        'module'     => $this->getModule(),
                        'controller' => 'logs',
                        'action'     => 'index',
                    ]
                ),
            ]
        );
        // Set view
        $this->view()->setTemplate('log-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }
}

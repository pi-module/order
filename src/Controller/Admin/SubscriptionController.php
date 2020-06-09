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

class SubscriptionController extends ActionController
{
    public function indexAction()
    {
        // Set view
        $this->view()->setTemplate('subscription-index');
    }

    public function detailAction()
    {
        // Get page
        $page       = $this->params('page', 1);

        // Get info
        $list   = [];
        $order  = ['time_create DESC', 'id DESC'];
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit  = intval($this->config('admin_perpage'));
        $where   = [];

        // Select
        $select = $this->getModel('subscription_detail')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('subscription_detail')->selectWith($select);

        // Make list
        foreach ($rowset as $row) {
            $list[$row->id]                       = $row->toArray();
        }

        // Get count
        $count     = ['count' => new Expression('count(*)')];
        $select    = $this->getModel('subscription_detail')->select()->columns($count);
        $count     = $this->getModel('subscription_detail')->selectWith($select)->current()->count;

        // Set paginator
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
                        'controller' => 'subscription',
                        'action'     => 'detail',
                    ]
                ),
            ]
        );

        // Set view
        $this->view()->setTemplate('subscription-detail');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function customerAction()
    {
        // Get page
        $page       = $this->params('page', 1);

        // Get info
        $list   = [];
        $order  = ['id DESC'];
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit  = intval($this->config('admin_perpage'));
        $where   = [];

        // Select
        $select = $this->getModel('subscription_customer')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('subscription_customer')->selectWith($select);

        // Make list
        foreach ($rowset as $row) {
            $list[$row->id]                       = $row->toArray();
        }

        // Get count
        $count     = ['count' => new Expression('count(*)')];
        $select    = $this->getModel('subscription_customer')->select()->columns($count);
        $count     = $this->getModel('subscription_customer')->selectWith($select)->current()->count;

        // Set paginator
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
                        'controller' => 'subscription',
                        'action'     => 'customer',
                    ]
                ),
            ]
        );

        // Set view
        $this->view()->setTemplate('subscription-customer');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function productAction()
    {
        // Get page
        $page       = $this->params('page', 1);

        // Get info
        $list   = [];
        $order  = ['id DESC'];
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit  = intval($this->config('admin_perpage'));
        $where   = [];

        // Select
        $select = $this->getModel('subscription_product')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('subscription_product')->selectWith($select);

        // Make list
        foreach ($rowset as $row) {
            $list[$row->id]                       = $row->toArray();
        }

        // Get count
        $count     = ['count' => new Expression('count(*)')];
        $select    = $this->getModel('subscription_product')->select()->columns($count);
        $count     = $this->getModel('subscription_product')->selectWith($select)->current()->count;

        // Set paginator
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
                        'controller' => 'subscription',
                        'action'     => 'product',
                    ]
                ),
            ]
        );

        // Set view
        $this->view()->setTemplate('subscription-product');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }
}
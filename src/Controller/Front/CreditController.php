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

namespace Module\Order\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Expression;

class CreditController extends IndexController
{
    public function indexAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        if (!$config['credit_active']) {
            $this->getResponse()->setStatusCode(401);
            $this->terminate(__('So sorry, At this moment credit is inactive'), '', 'error-denied');
            $this->view()->setLayout('layout-simple');
            return;
        }
        // Get page
        $page = $this->params('page', 1);
        // Get user info
        $user = Pi::api('user', 'order')->getUserInformation();
        // Get credit
        $credit = Pi::api('credit', 'order')->getCredit($user['id']);
        // Get info
        $perPage = 50;
        $list = array();
        $order = array('time_create DESC', 'id DESC');
        $offset = (int)($page - 1) * $perPage;
        $limit = intval($perPage);
        $where = array('uid' => $user['id']);
        // Select
        $select = $this->getModel('history')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('history')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['amount_view'] = Pi::api('api', 'order')->viewPrice($row->amount);
            $list[$row->id]['amount_old_view'] = Pi::api('api', 'order')->viewPrice($row->amount_old);
            $list[$row->id]['time_create_view'] = _date($row->time_create);

            $list[$row->id]['message_user'] = Pi::service('markup')->render($row->message_user, 'html', 'html');

            switch ($row->status_fluctuation) {
                case 'increase':
                    $list[$row->id]['status_fluctuation_view'] = __('Increase');
                    $list[$row->id]['status_fluctuation_class'] = 'label label-success';
                    break;

                case 'decrease':
                    $list[$row->id]['status_fluctuation_view'] = __('Decrease');
                    $list[$row->id]['status_fluctuation_class'] = 'label label-danger';
                    break;
            }

            switch ($row->status_action) {
                case 'automatic':
                    $list[$row->id]['status_action_view'] = __('Automatic');
                    break;

                case 'manual':
                    $list[$row->id]['status_action_view'] = __('Manual');
                    break;
            }

            if ($row->order > 0) {
                $list[$row->id]['orderLink'] = Pi::url($this->url('order', array(
                    'controller' => 'detail',
                    'id' => $row->order
                )));
            } elseif ($row->invoice > 0) {
                $invoice = Pi::api('invoice', 'order')->getInvoice($row->invoice);
                $list[$row->id]['orderLink'] = Pi::url($this->url('order', array(
                    'controller' => 'detail',
                    'id' => $invoice['order'],
                )));
            } else {
                $list[$row->id]['orderLink'] = '';
            }
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = $this->getModel('history')->select()->columns($count);
        $count = $this->getModel('history')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($perPage);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => $this->getModule(),
                'controller' => 'credit',
                'action' => 'index',
            )),
        ));
        // Set view
        $this->view()->setTemplate('credit');
        $this->view()->assign('user', $user);
        $this->view()->assign('credit', $credit);
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }
}
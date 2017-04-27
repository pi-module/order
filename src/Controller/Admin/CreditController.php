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
use Module\Order\Form\CreditForm;
use Module\Order\Form\CreditFilter;
use Zend\Json\Json;
use Zend\Db\Sql\Predicate\Expression;

class CreditController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        // Get module list
        $moduleList = Pi::registry('modulelist')->read();
        // Get info
        $list = array();
        $order = array('time_update DESC', 'id DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $select = $this->getModel('credit')->select()->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('credit')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['amount_view'] = Pi::api('api', 'order')->viewPrice($row->amount);
            $list[$row->id]['time_update_view'] = _date($row->time_update);
            $list[$row->id]['user'] = Pi::api('user', 'order')->getUserInformation($row->uid);
            $amountDetail = json::decode($row->amount_detail, true);
            $list[$row->id]['amount_detail_view'] = array();
            foreach ($amountDetail as $module => $amount) {
                $list[$row->id]['amount_detail_view'][$module] = array(
                    'module_name' => $module,
                    'module_title' => $moduleList[$module]['title'],
                    'amount' => $amount,
                    'amount_view' => Pi::api('api', 'order')->viewPrice($amount),
                );
            }
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = $this->getModel('credit')->select()->columns($count);
        $count = $this->getModel('credit')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
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
        $this->view()->setTemplate('credit-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function historyAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $uid = $this->params('uid');
        // Get info
        $list = array();
        $order = array('time_create DESC', 'id DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $where = array();
        // Get credit
        if ($uid > 0) {
            $credit = $this->getModel('credit')->find($uid, 'uid');
            $where['uid'] = $credit['uid'];
            $user = Pi::api('user', 'order')->getUserInformation($credit['uid']);
            // Set view
            $this->view()->assign('credit', $credit);
        }
        // Select
        $select = $this->getModel('history')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('history')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['amount_view'] = Pi::api('api', 'order')->viewPrice($row->amount);
            $list[$row->id]['amount_old_view'] = Pi::api('api', 'order')->viewPrice($row->amount_old);
            $list[$row->id]['amount_new_view'] = Pi::api('api', 'order')->viewPrice($row->amount_new);

            $list[$row->id]['time_create_view'] = _date($row->time_create);

            $list[$row->id]['message_user'] = Pi::service('markup')->render($row->message_user, 'html', 'html');
            $list[$row->id]['message_admin'] = Pi::service('markup')->render($row->message_admin, 'html', 'html');

            if (isset($user)) {
                $list[$row->id]['user'] = $user;
            } else {
                $list[$row->id]['user'] = Pi::api('user', 'order')->getUserInformation($row->uid);
            }

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
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = $this->getModel('history')->select()->columns($count);
        $count = $this->getModel('history')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => $this->getModule(),
                'controller' => 'history',
                'action' => 'index',
                'uid' => $uid,
            )),
        ));
        // Set view
        $this->view()->setTemplate('credit-history');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function updateAction()
    {
        // Set info
        $module = $this->params('module');
        $uid = $this->params('uid');
        $message = '';
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Set form option
        $option = array(
            'type' => $config['credit_type']
        );
        // Set form
        $form = new CreditForm('credit', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new CreditFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Add credit
                $result = Pi::api('credit', 'order')->addCredit(
                    $values['uid'],
                    $values['amount'],
                    $values['status_fluctuation'],
                    'manual',
                    $values['message_admin'],
                    $values['message_user'],
                    $values['module']
                );
                // Check result
                if ($result['status'] == 1) {
                    $message = __('Input amount add as user credit successfully.');
                    $this->jump(array('action' => 'update'), $message);
                } else {
                    $message = $result['message'];
                    $form->setData($values);
                }
            }
        } elseif (intval($uid) > 0) {
            $values = array(
                'uid' => intval($uid),
            );
            $form->setData($values);
        }
        // Set view
        $this->view()->setTemplate('credit-update');
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->assign('config', $config);
    }
}
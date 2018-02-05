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
use Module\Order\Form\PromocodeForm;
use Module\Order\Form\PromocodeFilter;

class PromocodeController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        
        // Get info
        $select = Pi::model('promocode', 'order')->select()->order('id DESC');
        
        // Set paginator
        $resultSetPrototype = new  \Zend\Db\ResultSet\ResultSet();
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect(
            $select,
            Pi::model('promocode', 'order')->getAdapter(),
            $resultSetPrototype
        );      
        
        $paginator = new \Pi\Paginator\Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($this->config('view_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
        'router' => $this->getEvent()->getRouter(),
        'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => 'order',
                'controller' => 'promocode',
                'action' => 'index',
            )),
        ));
              
        // Set view
        $this->view()->setTemplate('promocode-index');
        $this->view()->assign('paginator', $paginator);
        $this->view()->assign('modules', Pi::model('promocode', 'order')->getModules());
    }

    public function manageAction()
    {
        $id = $this->params('id');
        
        $form = new PromocodeForm($id, Pi::model('promocode', 'order')->getModules());
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PromocodeFilter(array('id' => $id)));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $values['module'] = json_encode($values['module']);
                
                if (!empty($values['id'])) {
                    $row = $this->getModel('promocode')->find($values['id']);
                } else {
                    $row = $this->getModel('promocode')->createRow();
                }
                    
                $row->assign($values);
                $row->save();
                    
                $this->jump(array('action' => 'index'));
                
            }
        }  else {
            if ($id) {
                $where = array('id' => $id);
                $select = Pi::model("promocode", 'order')->select()->where($where);
                $row = Pi::model("promocode", 'order')->selectWith($select)->current();
                $data = $row->toArray();
                $data['module'] = json_decode($data['module'], true);
                
                $form->setData($data);    
            }
        }
        
        $this->view()->setTemplate('promocode-manage');
        $this->view()->assign('form', $form);
    }
    
    
}

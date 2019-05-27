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

use Module\Order\Form\GatewayFilter;
use Module\Order\Form\GatewayForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Json\Json;

class GatewayController extends ActionController
{
    protected $gatewayColumns
        = [
            'id', 'title', 'path', 'description', 'image', 'status', 'type', 'option',
        ];

    public function indexAction()
    {
        $list = Pi::api('gateway', 'order')->getAllGatewayList();
        $this->view()->assign('list', $list);
        $this->view()->setTemplate('gateway-index');
    }

    public function updateAction()
    {
        $gateway = $this->params('path');
        $gateway = Pi::api('gateway', 'order')->getGateway($gateway);
        $message = null;

        // Set form
        $form = new GatewayForm('gateway', $gateway->gatewaySettingForm);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new GatewayFilter($gateway->gatewaySettingForm));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Set option
                foreach ($values as $key => $value) {
                    $values['option'][$key] = $value;
                }
                // Set just gateway fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->gatewayColumns)) {
                        unset($values[$key]);
                    }
                }
                // Set values
                $values['title']       = $gateway->gatewayInformation['title'];
                $values['description'] = $gateway->gatewayInformation['description'];
                $values['status']      = 1;
                $values['option']      = Json::encode($values['option']);
                // Save values
                if ($gateway->gatewayIsActive == -1) {
                    $row = $this->getModel('gateway')->createRow();
                } else {
                    $row = $this->getModel('gateway')->find($values['path'], 'path');
                }
                $row->assign($values);
                $row->save();
                // Set jump
                $message = __('Gateway data saved successfully.');
                $url     = ['controller' => 'gateway', 'action' => 'index'];
                $this->jump($url, $message);
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $values['path'] = $gateway->gatewayAdapter;
            if (!empty($gateway->gatewayOption)) {
                foreach ($gateway->gatewayOption as $key => $value) {
                    $values[$key] = $value;
                }
            }
            $form->setData($values);
        }
        $this->view()->assign('form', $form);
        $this->view()->assign('title', sprintf(__('Install %s gateway'), $gateway->gatewayInformation['title']));
        $this->view()->assign('message', $message);
        $this->view()->assign('description', $gateway->getDescription());
        $this->view()->setTemplate('gateway-update');
    }

    public function statusAction()
    {
        $gateway = $this->params('path');
        $gateway = $this->getModel('gateway')->find($gateway, 'path');
        if ($gateway) {
            if ($gateway->status == 1) {
                $gateway->status = 0;
            } elseif ($gateway->status == 0) {
                $gateway->status = 1;
            }
            $gateway->save();
            // Set jump
            $message = __('Gateway data saved successfully.');
            $url     = ['controller' => 'gateway', 'action' => 'index'];
            $this->jump($url, $message);
        } else {
            // Set jump
            $message = __('Please select installed gateway');
            $url     = ['controller' => 'gateway', 'action' => 'index'];
            $this->jump($url, $message);
        }
    }
}
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

use Module\Order\Form\DeliveryFilter;
use Module\Order\Form\DeliveryForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class DeliveryController extends ActionController
{
    /**
     * delivery Columns
     */
    protected $deliveryColumns
        = [
            'id', 'title', 'status',
        ];

    /**
     * delivery_gateway Columns
     */
    protected $deliverygatewayColumns
        = [
            'id', 'delivery', 'gateway',
        ];

    public function indexAction()
    {
        // Get info
        $list   = [];
        $order  = ['id DESC'];
        $select = $this->getModel('delivery')->select()->order($order);
        $rowset = $this->getModel('delivery')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
        }
        // Set view
        $this->view()->setTemplate('delivery-index');
        $this->view()->assign('list', $list);
    }

    public function updateAction()
    {
        // Get id
        $id = $this->params('id');
        // Set form
        $form = new DeliveryForm('delivery');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new DeliveryFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Get gateway
                $gateways = $values['gateway'];
                // Set just delivery fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->deliveryColumns)) {
                        unset($values[$key]);
                    }
                }
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('delivery')->find($values['id']);
                } else {
                    $row = $this->getModel('delivery')->createRow();
                }
                $row->assign($values);
                $row->save();
                // Save gateway
                $this->getModel('delivery_gateway')->delete(['delivery' => $row->id]);
                if (is_array($gateways)) {
                    foreach ($gateways as $gateway) {
                        $delivery_gateway           = $this->getModel('delivery_gateway')->createRow();
                        $delivery_gateway->delivery = $row->id;
                        $delivery_gateway->gateway  = $gateway;
                        $delivery_gateway->save();
                    }
                } else {
                    $delivery_gateway           = $this->getModel('delivery_gateway')->createRow();
                    $delivery_gateway->delivery = $row->id;
                    $delivery_gateway->gateway  = $gateways;
                    $delivery_gateway->save();
                }
                // Add log
                //$operation = (empty($values['id'])) ? 'add' : 'edit';
                //Pi::api('log', 'shop')->addLog('delivery', $row->id, $operation);
                // Check it save or not
                $message = __('Delivery data saved successfully.');
                $this->jump(['action' => 'index'], $message);
            }
        } else {
            if ($id) {
                $values = $this->getModel('delivery')->find($id)->toArray();
                // Get gateways
                $where             = ['delivery' => $id];
                $select            = $this->getModel('delivery_gateway')->select()->where($where)->limit(1);
                $gateway           = $this->getModel('delivery_gateway')->selectWith($select)->current();
                $values['gateway'] = $gateway['gateway'];
                // Set form data
                $form->setData($values);
            }
        }
        // Set view
        $this->view()->setTemplate('delivery-update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a delivery'));
    }
}

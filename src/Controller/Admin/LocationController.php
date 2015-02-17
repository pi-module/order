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

namespace Module\Shop\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\Shop\Form\DeliveryForm;
use Module\Shop\Form\DeliveryFilter;
use Module\Shop\Form\LocationForm;
use Module\Shop\Form\LocationFilter;

class CheckoutController extends ActionController
{
    /**
     * delivery Columns
     */
    protected $deliveryColumns = array(
        'id', 'title', 'status'
    );

    /**
     * delivery_payment Columns
     */
    protected $deliveryPaymentColumns = array(
        'id', 'delivery', 'payment'
    );

    /**
     * location Columns
     */
    protected $locationColumns = array(
        'id', 'paren', 'title', 'status'
    );

    /**
     * location_delivery Columns
     */
    protected $locationDeliveryColumns = array(
        'id', 'location', 'delivery', 'price', 'delivery_time'
    );

    public function indexAction()
    {
    	$this->view()->setTemplate('checkout_index');
    }

    public function locationAction()
    {
        // Get info
        $list = array();
        $order = array('id DESC');
        $select = $this->getModel('location')->select()->order($order);
        $rowset = $this->getModel('location')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
        }
        // Go to update page if empty
        if (empty($list)) {
            return $this->redirect()->toRoute('', array('action' => 'locationUpdate'));
        }
        // Set view
        $this->view()->setTemplate('checkout_location');
        $this->view()->assign('list', $list);
    }

    public function locationUpdateAction()
    {
        // Get id
        $id = $this->params('id');
        $option = array();
        //
        $where = array('status' => '1');
        $order = array('id DESC');
        $select = $this->getModel('delivery')->select()->where($where)->order($order);
        $rowset = $this->getModel('delivery')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $option['delivery'][$row->id] = $row->toArray();
        }
        // Go to update page if empty
        if (empty($option['delivery'])) {
            return $this->redirect()->toRoute('', array('action' => 'deliveryUpdate'));
        }
        // Set form
        $form = new LocationForm('location', $option);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new LocationFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Set location_delivery array
                $ld = array();
                foreach ($option['delivery'] as $delivery) {
                    $delivery_active = sprintf('delivery_active_%s', $delivery['id']);
                    $delivery_price = sprintf('delivery_price_%s', $delivery['id']);
                    $delivery_time = sprintf('delivery_time_%s', $delivery['id']);
                    if ($values[$delivery_active]) {
                        $ld[$delivery['id']]['delivery'] = $delivery['id'];
                        $ld[$delivery['id']]['price'] = $values[$delivery_price];
                        $ld[$delivery['id']]['delivery_time'] = $values[$delivery_time];
                    }
                }
                // Set just delivery fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->locationColumns)) {
                        unset($values[$key]);
                    }
                }
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('location')->find($values['id']);
                } else {
                    $row = $this->getModel('location')->createRow();
                }
                $row->assign($values);
                $row->save();
                // Save payment
                $this->getModel('location_delivery')->delete(array('location' => $row->id));
                foreach ($ld as $item) {
                    $location_delivery = $this->getModel('location_delivery')->createRow();
                    $location_delivery->location = $row->id;
                    $location_delivery->delivery = $item['delivery'];
                    $location_delivery->price = $item['price'];
                    $location_delivery->delivery_time = $item['delivery_time'];
                    $location_delivery->save();
                }
                // Add log
                $operation = (empty($values['id'])) ? 'add' : 'edit';
                Pi::api('log', 'shop')->addLog('location', $row->id, $operation);
                // Check it save or not
                if ($row->id) {
                    $message = __('Location data saved successfully.');
                    $this->jump(array('action' => 'location'), $message);
                } else {
                    $message = __('Location data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }  
        } else {
            if ($id) {
                $values = $this->getModel('location')->find($id)->toArray();
                // Set location_delivery
                $where = array('location' => $values['id']);
                $select = $this->getModel('location_delivery')->select()->where($where);
                $rowset = $this->getModel('location_delivery')->selectWith($select)->toArray();
                foreach ($rowset as $ld) {
                    $values[sprintf('delivery_active_%s', $ld['delivery'])] = 1;
                    $values[sprintf('delivery_price_%s', $ld['delivery'])] = $ld['price'];
                    $values[sprintf('delivery_time_%s', $ld['delivery'])] = $ld['delivery_time'];
                }
                // Set to form
                $form->setData($values);
                $message = 'You can edit this location';
            } else {
                $message = 'You can add new location';
            }
        }
        // Set view
        $this->view()->setTemplate('checkout_location_update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a location'));
        $this->view()->assign('message', $message);
    }	

    public function deliveryAction()
    {
        // Get info
        $list = array();
        $order = array('id DESC');
        $select = $this->getModel('delivery')->select()->order($order);
        $rowset = $this->getModel('delivery')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
        }
        // Go to update page if empty
        if (empty($list)) {
            return $this->redirect()->toRoute('', array('action' => 'deliveryUpdate'));
        }
        // Set view
        $this->view()->setTemplate('checkout_delivery');
        $this->view()->assign('list', $list);
    }

    public function deliveryUpdateAction()
    {
        // Get id
        $id = $this->params('id');
        // Set form
        $form = new DeliveryForm('delivery');
        $form->setAttribute('enctype', 'multipart/form-data');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new DeliveryFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Get payment
                $payments = $values['payment'];
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
                // Save payment
                $this->getModel('delivery_payment')->delete(array('delivery' => $row->id));
                foreach ($payments as $payment) {
                    $delivery_payment = $this->getModel('delivery_payment')->createRow();
                    $delivery_payment->delivery = $row->id;
                    $delivery_payment->payment = $payment;
                    $delivery_payment->save();
                }
                // Add log
                $operation = (empty($values['id'])) ? 'add' : 'edit';
                Pi::api('log', 'shop')->addLog('delivery', $row->id, $operation);
                // Check it save or not
                if ($row->id) {
                    $message = __('Delivery data saved successfully.');
                    $this->jump(array('action' => 'delivery'), $message);
                } else {
                    $message = __('Delivery data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }   
        } else {
            if ($id) {
                $values = $this->getModel('delivery')->find($id)->toArray();
                // Get payments
                $where = array('delivery' => $id);
                $select = $this->getModel('delivery_payment')->select()->where($where);
                $rowset = $this->getModel('delivery_payment')->selectWith($select)->toArray();
                foreach ($rowset as $payment) {
                    $values['payment'][] = $payment['payment'];
                }
                // Set form data
                $form->setData($values);
                $message = 'You can edit this delivery';
            } else {
                $message = 'You can add new delivery';
            }
        }
        // Set view
        $this->view()->setTemplate('checkout_delivery_update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a delivery'));
        $this->view()->assign('message', $message);
    }
}
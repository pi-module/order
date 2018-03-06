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
use Module\Order\Form\InstallmentForm;
use Module\Order\Form\InstallmentFilter;

class InstallmentController extends ActionController
{
    public function editAction()
    {
        // Get id
        $id = $this->params('id');
        $installment = $this->getModel('invoice_installment')->find($id);
        $invoice = Pi::api('invoice', 'order')->getInvoice($installment->invoice);
       
        if ($invoice['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED) {
            $message = __('Your cannont edit installment. Invoice was cancelled.');
            $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $invoice['order']), $message);  
        }
        
        $type = 'offline';
        $gateway = Pi::api('gateway', 'order')->getGateway($installment->gateway);
        if ($gateway) {
            $type = $gateway->gatewayRow['type'];
        }
        
        $readonly = $installment->status_payment == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID && $type == 'online';
        $options = array(
            'readonly' => $readonly 
        );
        $form = new InstallmentForm('installmentForm', $options);
        
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new InstallmentFilter($options));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                
                // Save values
                $row = $this->getModel('invoice_installment')->find($id);
                $row->assign($values);
                if (!$readonly) {
                    $row->save();
                }
                
                $message = __('Your installment data saved successfully.');
                $this->jump(array('controller' => 'order', 'action' => 'view', 'id' => $invoice['order']), $message);
            }
        } else {
            $form->setData($installment->toArray());
        }
        // Set view
        $this->view()->setTemplate('installment-edit');
        $this->view()->assign('form', $form);
        
    }
}

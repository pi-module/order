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

use Module\Order\Form\InvoiceFilter;
use Module\Order\Form\InvoiceForm;
use Module\Order\Form\InvoiceSettingFilter;
use Module\Order\Form\InvoiceSettingForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Json\Json;

class InvoiceController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page           = $this->params('page', 1);
        $module         = $this->params('module');
        $orderid        = $this->params('orderid');
        $randomid       = $this->params('randomid');
        $uid            = $this->params('uid');
        $payment_status = $this->params('payment_status');
        $start          = $this->params('start');
        $end            = $this->params('end');
        // Get info
        $list   = [];
        $order  = ['id DESC', 'time_create DESC'];
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit  = intval($this->config('admin_perpage'));
        $where  = [];
        // Set where
        if ($orderid) {
            $where['order'] = $orderid;
        }
        if ($randomid) {
            $where['random_id'] = $randomid;
        }
        if ($uid) {
            $where['uid'] = $uid;
        }
        if ($payment_status) {
            if ($payment_status == 'delayed') {
                $where['status']            = 2;
                $where['time_duedate <= ?'] = time();
            } elseif (in_array($payment_status, [1, 2])) {
                $where['status'] = [1, 2];
            }
        }
        if ($start) {
            $where['time_duedate >= ?'] = strtotime($start);
        }
        if ($end) {
            $where['time_duedate <= ?'] = strtotime($end);
        }
        // Select
        $invoiceTable            = Pi::model('invoice', 'order')->getTable();
        $orderTable              = Pi::model("order", 'order')->getTable();
        $invoiceInstallmentTable = Pi::model("invoice_installment", 'order')->getTable();
        $orderAddressTable       = Pi::model("order_address", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['invoice' => $invoiceTable])
            ->join(['order' => $orderTable], 'invoice.order = order.id', [])
            ->join(
                ['order_address' => $orderAddressTable], 'order_address.order = order.id',
                ['id_number', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company',
                 'company_id', 'company_vat', 'delivery', 'location']
            )
            ->join(
                ['invoice_installment' => $invoiceInstallmentTable],
                new Expression('invoice_installment.invoice = invoice.id AND invoice_installment.time_duedate <' . time()),
                ['status_payment' => new Expression("MIN(status_payment)")], 'left'
            )
            ->where($where)->order($order)->offset($offset)->limit($limit)
            ->group('invoice.id');

        $rowset = Pi::db()->query($select);

        // Make list
        foreach ($rowset as $row) {

            $list[$row['id']] = Pi::api('invoice', 'order')->canonizeInvoice($row);
            // set Products
            $options    = [
                'invoice'     => $row['id'],
                'time_create' => $row['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED ? $row['time_cancel'] : time(),
            ];
            $products   = Pi::api('order', 'order')->listProduct($row['order'], $options);
            $totalPrice = 0;
            foreach ($products as $product) {
                $totalPrice += $product['product_price'] + $product['shipping_price'] + $product['packing_price'] + $product['setup_price']
                    + $product['vat_price'] - $product['discount_price'];
            }
            $list[$row['id']]['total_price_view'] = Pi::api('api', 'order')->viewPrice($totalPrice);
        }
        // Set paginator
        $count  = ['count' => new Expression('count(*)')];
        $select = Pi::db()->select();
        $select
            ->from(['invoice' => $invoiceTable])->columns($count)
            ->join(['order' => $orderTable], 'invoice.order = order.id', [])
            ->where($where);

        $count     = Pi::db()->query($select)->current()['count'];
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(
            [
                'router' => $this->getEvent()->getRouter(),
                'route'  => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
                'params' => array_filter(
                    [
                        'module'         => $this->getModule(),
                        'controller'     => 'invoice',
                        'action'         => 'index',
                        'orderid'        => $orderid,
                        'randomid'       => $randomid,
                        'uid'            => $uid,
                        'payment_status' => $payment_status,
                        'start'          => $start,
                        'end'            => $end,
                    ]
                ),
            ]
        );
        // Set form
        $values = [
            'orderid'        => $orderid,
            'randomid'       => $randomid,
            'uid'            => $uid,
            'payment_status' => $payment_status,
            'start'          => $start,
            'end'            => $end,
        ];
        $form   = new InvoiceSettingForm('setting');
        $form->setAttribute('action', $this->url('', ['action' => 'process']));
        $form->setData($values);
        // Set view
        $this->view()->setTemplate('invoice-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
        $this->view()->assign('form', $form);
    }

    public function processAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new InvoiceSettingForm('setting');
            $form->setInputFilter(new InvoiceSettingFilter());
            $form->setData($data);
            if ($form->isValid()) {
                $values  = $form->getData();
                $message = __('Go to filter');
                $url     = [
                    'action'         => 'index',
                    'orderid'        => $values['orderid'],
                    'randomid'       => $values['randomid'],
                    'uid'            => $values['uid'],
                    'payment_status' => $values['payment_status'],
                    'start'          => $values['start'],
                    'end'            => $values['end'],
                ];
            } else {
                $message = __('Not valid');
                $url     = [
                    'action' => 'index',
                ];
            }
        } else {
            $message = __('Not set');
            $url     = [
                'action' => 'index',
            ];
        }
        return $this->jump($url, $message);
    }

    public function viewAction()
    {
        // Get id
        $id = $this->params('id');
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get info
        $invoice                 = Pi::api('invoice', 'order')->getInvoice($id);
        $invoice['installments'] = Pi::api('installment', 'order')->getInstallmentsFromInvoice($invoice['id']);
        $order                   = Pi::api('order', 'order')->getOrder($invoice['order']);
        $addressInvoicing        = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $addressDelivery         = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'DELIVERY');

        // Get product list
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id']);
        // Check invoice
        if (empty($invoice) || empty($order)) {
            $this->jump(['', 'action' => 'index'], __('The invoice not found.'));
        }
        // Get logs
        $invoice['log'] = Pi::api('log', 'order')->getLog($invoice['order']);
        // set view
        $this->view()->setTemplate('invoice-view');
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
        $this->view()->assign('config', $config);
        $this->view()->assign('addressInvoicing', $addressInvoicing);
        $this->view()->assign('addressDelivery', $addressDelivery);
        $this->view()->assign('gateways', Pi::api('gateway', 'order')->getAdminGatewayList());

    }

    public function addAction()
    {
        // Get id
        $order = $this->params('order');
        // Get order
        $invoices = Pi::api('invoice', 'order')->getInvoiceFromOrder($order);
        if (Pi::api('order', 'order')->hasValidInvoice($order) || Pi::api('order', 'order')->hasDraftInvoice($order)) {
            $message = __('Order already have valid invoice. You cannot add another one');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order], $message);
        }

        $order   = $this->getModel('order')->find($order);
        $order   = Pi::api('order', 'order')->canonizeOrder($order);
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        // Set form
        $form = new InvoiceForm('setting');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new InvoiceFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Save values
                $invoice = [
                    'order'        => $order['id'],
                    'time_create'  => time(),
                    'time_invoice' => strtotime($values['time_invoice']),
                    'random_id'    => time() + rand(100, 999),
                    'code'         => Pi::api('invoice', 'order')->generatCode(),
                    'create_by'    => 'ADMIN',
                    'status'       => \Module\Order\Model\Invoice::STATUS_INVOICE_DRAFT,
                    'type_payment' => $values['type_payment'],
                ];
                $row     = $this->getModel('invoice')->createRow();
                $row->assign($invoice);
                $row->save();

                // Check it save or not
                $message = __('New invoice data saved successfully.');
                $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order['id']], $message);
            }
        }
        // Set view
        $this->view()->setTemplate('invoice-add');
        $this->view()->assign('form', $form);
        $this->view()->assign('order', $order);
        $this->view()->assign('address', $address);
    }

    public function editAction()
    {
        // Get id
        $id = $this->params('id');
        // Get invoice and order
        $invoice = Pi::api('invoice', 'order')->getInvoice($id);

        if ($invoice['status'] != \Module\Order\Model\Invoice::STATUS_INVOICE_DRAFT) {
            $message = __('Invoice was validated or cancelled. You cannont edit it.');
            $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $invoice['order']], $message);
        }
        $order   = Pi::api('order', 'order')->getOrder($invoice['order']);
        $address = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');


        // Set form
        $form = new InvoiceForm('invoice');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new InvoiceFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values                 = $form->getData();
                $values['time_duedate'] = strtotime($values['time_duedate']);
                $values['total_price']  = $values['product_price'] + $values['shipping_price'] + $values['packing_price'] + $values['setup_price']
                    + $values['vat_price'];
                $values['time_invoice'] = strtotime($values['time_invoice']);
                // Save values
                $row = $this->getModel('invoice')->find($id);
                $row->assign($values);
                $row->save();
                // Get new invoice
                $newInvoice = Pi::api('invoice', 'order')->getInvoice($id);
                // Save log
                Pi::service('audit')->log('invoice', $invoice);
                Pi::service('audit')->log('invoice', $newInvoice);
                Pi::service('audit')->log('invoice', '-----------------------------------------');
                // Check it save or not
                $message = __('Your invoice data saved successfully.');
                $this->jump(['controller' => 'order', 'action' => 'view', 'id' => $order['id']], $message);
            }
        } else {
            $invoice['time_duedate'] = date('Y-m-d', $invoice['time_duedate']);
            $form->setData($invoice);
        }
        // Set view
        $this->view()->setTemplate('invoice-edit');
        $this->view()->assign('form', $form);
        $this->view()->assign('invoice', $invoice);
        $this->view()->assign('order', $order);
        $this->view()->assign('address', $address);
    }

    public function printPdfAction()
    {
        $id  = $this->params('id');
        $ret = Pi::api('invoice', 'order')->pdf($id, false);
        if (!$ret['status']) {
            $this->jump(['', 'controller' => 'index', 'action' => 'index'], $ret['message']);
        }

    }
}

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

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Laminas\Json\Json;
use Laminas\Math\Rand;

/*
 * Pi::api('order', 'order')->getOrder($id);
 * Pi::api('order', 'order')->getOrderFromUser($uid, $compressed);
 * Pi::api('order', 'order')->generatCode($id);
 * Pi::api('order', 'order')->orderStatus($status);
 * Pi::api('order', 'order')->paymentStatus($status);
 * Pi::api('order', 'order')->deliveryStatus($status);
 * Pi::api('order', 'order')->canonizeOrder($order);
 * Pi::api('order', 'order')->listProduct($id);
 * Pi::api('order', 'order')->listAllProduct($module);
 * Pi::api('order', 'order')->updateOrder($id, $invoice);
 * Pi::api('order', 'order')->setOrderInfo($order);
 * Pi::api('order', 'order')->getOrderInfo();
 * Pi::api('order', 'order')->updateOrderInfo($order);
 * Pi::api('order', 'order')->unsetOrderInfo();
 * Pi::api('order', 'order')->getDetail($where);
 * Pi::api('order', 'order')->autoCancelOrder();
 */

class Order extends AbstractApi
{
    public function checkProduct()
    {
        return true;
    }

    public function getOrder($id)
    {
        $order = Pi::model('order', 'order')->find($id);
        $order = $this->canonizeOrder($order);
        return $order;
    }

    public function getOrderFromUser($uid, $compressed = false, $options = [])
    {
        $orders = [];
        // Check compressed
        if ($compressed) {
            $where = ['uid' => $uid, 'status_order' => \Module\Order\Model\Order::STATUS_ORDER_VALIDATED];
        } else {
            $where = ['uid' => $uid];
        }

        if (!isset($options['draft']) || !$options['draft']) {
            $where['status_order != ?'] = \Module\Order\Model\Order::STATUS_ORDER_DRAFT;
        }

        $order = ['id DESC'];
        // Select
        $select = Pi::model('order', $this->getModule())->select()->where($where)->order($order);
        if (isset($options['limit'])) {
            $select->limit($options['limit']);
        }
        if (isset($options['offset'])) {
            $select->offset($options['offset']);
        }
        $rowset = Pi::model('order', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $orders[$row->id] = $this->canonizeOrder($row);
        }
        return $orders;
    }

    public function generatCode($year = null)
    {
        $config = Pi::service('registry')->config->read($this->getModule());

        $year  = $year ?: date('Y');
        $count = Pi::model('order', 'order')->count(
            ['time_create >= ' . strtotime('01-01-' . $year) . ' AND time_create < ' . strtotime('01-01-' . ($year + 1))]
        );
        $num   = $year . sprintf('%03d', ($count + 1));

        return sprintf('%s-%s', $config['order_code_prefix'], $num);
    }

    public function orderStatus($status)
    {
        $return = [];
        switch ($status) {
            case \Module\Order\Model\Order::STATUS_ORDER_PENDING:
            case \Module\Order\Model\Order::STATUS_ORDER_DRAFT:
                $return['orderClass']   = 'btn-warning';
                $return['orderLabel']   = 'badge-warning';
                $return['orderTableBg'] = 'warning';
                $return['orderBg']      = 'bg-warning';
                break;

            case \Module\Order\Model\Order::STATUS_ORDER_VALIDATED:
                $return['orderClass']   = 'btn-success';
                $return['orderLabel']   = 'badge-success';
                $return['orderTableBg'] = 'success';
                $return['orderBg']      = 'bg-success';
                break;

            case \Module\Order\Model\Order::STATUS_ORDER_CANCELLED:
                $return['orderClass']   = 'btn-danger';
                $return['orderLabel']   = 'badge-danger';
                $return['orderTableBg'] = 'danger';
                $return['orderBg']      = 'bg-danger';
                break;
        }
        $return['orderTitle'] = \Module\Order\Model\Order::getStatusList()[$status];

        return $return;
    }

    public function canPayStatus($status)
    {
        $return = [];
        switch ($status) {
            case '1':
                $return['canPayClass'] = 'btn-success';
                $return['canPayLabel'] = 'badge-success';
                $return['canPayTitle'] = __('Can pay');
                break;

            case '2':
                $return['canPayClass'] = 'btn-warning';
                $return['canPayLabel'] = 'badge-warning';
                $return['canPayTitle'] = __('Can not pay');
                break;
        }
        return $return;
    }

    public function invoiceStatus($status)
    {
        $return = [];
        switch ($status) {
            case \Module\Order\Model\Invoice::STATUS_INVOICE_DRAFT:
                $return['invoiceClass'] = 'btn-warning';
                $return['invoiceLabel'] = 'badge-warning';
                break;

            case \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED:
                $return['invoiceClass'] = 'btn-success';
                $return['invoiceLabel'] = 'badge-success';
                break;
            case \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED:
                $return['invoiceClass'] = 'btn-danger';
                $return['invoiceLabel'] = 'badge-danger';
                break;

        }
        $return['invoiceTitle'] = \Module\Order\Model\Invoice::getStatusList()[$status];


        return $return;
    }

    public function deliveryStatus($status)
    {
        $return = [];
        switch ($status) {
            case '1':
                $return['deliveryClass'] = 'btn-warning';
                $return['deliveryLabel'] = 'badge-warning';
                $return['deliveryTitle'] = __('Not processed');
                break;

            case '2':
                $return['deliveryClass'] = 'btn-info';
                $return['deliveryLabel'] = 'badge-info';
                $return['deliveryTitle'] = __('Packed');
                break;

            case '3':
                $return['deliveryClass'] = 'btn-info';
                $return['deliveryLabel'] = 'badge-info';
                $return['deliveryTitle'] = __('Posted');
                break;

            case '4':
                $return['deliveryClass'] = 'btn-success';
                $return['deliveryLabel'] = 'badge-success';
                $return['deliveryTitle'] = __('Delivered');
                break;

            case '5':
                $return['deliveryClass'] = 'btn-danger';
                $return['deliveryLabel'] = 'badge-danger';
                $return['deliveryTitle'] = __('Back eaten');
                break;
        }
        return $return;
    }

    public function canonizeOrder($order)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set date_format
        $pattern = !empty($config['date_format']) ? $config['date_format'] : 'yyyy-MM-dd';
        // boject to array
        if (is_object($order)) {
            $order = $order->toArray();
        }
        // Set time_create_view
        $order['time_create_view'] = _date($order['time_create'], ['pattern' => $pattern]);
        // Set time_delivery_view
        $order['time_delivery_view'] = ($order['time_delivery']) ? _date($order['time_delivery'], ['pattern' => $pattern]) : __('Not Delivery');
        // Set user
        $order['user'] = Pi::api('user', 'order')->getUserInformation($order['uid']);
        // Set url_update_order
        $order['url_update_order'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'updateOrder',
                    'id'         => $order['id'],
                ]
            )
        );
        // Set url_update_payment
        $order['url_update_invoice'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'updateInvoice',
                ]
            )
        );
        // Set url_update_delivery
        $order['url_update_delivery'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'updateDelivery',
                    'id'         => $order['id'],
                ]
            )
        );
        // Set url_update_delivery
        $order['url_update_canPay'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'updateCanPay',
                    'id'         => $order['id'],
                ]
            )
        );
        //
        $order['url_update_note'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'updateNote',
                    'id'         => $order['id'],
                ]
            )
        );
        // Set url_edit
        $order['url_edit'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'edit',
                    'id'         => $order['id'],
                ]
            )
        );
        // Set url_print
        $order['url_print'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'print',
                    'id'         => $order['id'],
                ]
            )
        );
        // Set url_view
        $order['url_view'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'view',
                    'id'         => $order['id'],
                ]
            )
        );
        // Set url_view
        $order['url_list_user'] = Pi::url(
            Pi::service('url')->assemble(
                'admin', [
                    'controller' => 'order',
                    'action'     => 'listUser',
                    'uid'        => $order['uid'],
                ]
            )
        );
        // Status order
        $status_order          = $this->orderStatus($order['status_order']);
        $order['orderClass']   = $status_order['orderClass'];
        $order['orderLabel']   = $status_order['orderLabel'];
        $order['orderTitle']   = $status_order['orderTitle'];
        $order['orderTableBg'] = $status_order['orderTableBg'];
        $order['orderBg']      = $status_order['orderBg'];
        // Status delivery
        $status_delivery        = $this->deliveryStatus($order['status_delivery']);
        $order['deliveryClass'] = $status_delivery['deliveryClass'];
        $order['deliveryLabel'] = $status_delivery['deliveryLabel'];
        $order['deliveryTitle'] = $status_delivery['deliveryTitle'];
        //
        $can_pay              = $this->canPayStatus($order['can_pay']);
        $order['canPayClass'] = isset($can_pay['canPayClass']) ? $can_pay['canPayClass'] : null;
        $order['canPayLabel'] = isset($can_pay['canPayLabel']) ? $can_pay['canPayLabel'] : null;
        $order['canPayTitle'] = isset($can_pay['canPayTitle']) ? $can_pay['canPayTitle'] : null;
        //
        if ($order['type_commodity'] == 'product') {
            $order['type_commodity_view'] = __('Product');
        } elseif ($order['type_commodity'] == 'service') {
            $order['type_commodity_view'] = __('Service');
        } elseif ($order['type_commodity'] == 'booking') {
            $order['type_commodity_view'] = __('Booking');
        }

        $order['shortStatus'] = $order['orderTitle'];
        $order['shortLabel']  = $order['orderLabel'];
        // Set text_summary
        $order['user_note'] = Pi::service('markup')->render($order['user_note'], 'html', 'text');
        // Set text_summary
        $order['admin_note'] = Pi::service('markup')->render($order['admin_note'], 'html', 'text');

        $order['time_order']      = date('Y-m-d', $order['time_order']);
        $order['time_order_view'] = _date($order['time_order']);

        // return order
        return $order;
    }

    public function listProduct($id, $options = ['credit' => false, 'time_create' => 0])
    {
        $list   = [];
        $where  = ['order' => $id];
        $select = Pi::model('detail', 'order')->select()->where($where);
        $rowset = Pi::model('detail', 'order')->selectWith($select);
        foreach ($rowset as $row) {
            if (isset($options['module']) && $options['module'] != $row->module) {
                continue;
            }
            if (isset($options['credit']) && $options['credit']) {
                if ($row->product_type != 'credit' || $row->module != 'order') {
                    continue;
                }
                $extra = json_decode($row->extra, true);
                if ($extra['invoice'] != $options['invoice']) {
                    continue;
                }
            } else {
                if ($row->product_type == 'credit' && $row->module == 'order') {
                    continue;
                }
                if (isset($options['time_create']) && $options['time_create'] > 0 && $row->time_create > $options['time_create']) {
                    continue;
                }
            }

            $list[$row->id] = $row->toArray();
            if ($row->module != 'order' && Pi::service('module')->isActive($row->module)) {
                $extra                     = json_decode($row->extra, true);
                $extra['order']            = isset($options['order']) ? $options['order'] : false;
                $list[$row->id]['details'] = Pi::api('order', $row->module)->getProductDetails($row->product, $extra);
            } else {
                $list[$row->id]['details'] = [
                    'title'      => __('Manually order'),
                    'productUrl' => '',
                    'thumbUrl'   => '',
                ];
            }

            if (empty($row->extra)) {
                $list[$row->id]['extra'] = [];
            } else {
                $list[$row->id]['extra'] = json::decode($row->extra, true);
            }
        }

        return $list;
    }

    public function listAllProduct($module)
    {
        $list   = [];
        $select = Pi::model('detail', $this->getModule())->select();
        $rowset = Pi::model('detail', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $list[$row->id]            = $row->toArray();
            $list[$row->id]['details'] = Pi::api('order', $module)->getProductDetails($row->product, json_decode($row->extra, true));
            if (empty($row->extra)) {
                $list[$row->id]['extra'] = [];
            } else {
                $list[$row->id]['extra'] = json::decode($row->extra, true);
            }
        }
        return $list;
    }

    public function updateOrder($orderId, $invoiceId)
    {
        // Get order
        $order = Pi::model('order', $this->getModule())->find($orderId);
        // Get invoice
        $invoice = Pi::api('invoice', 'order')->getInvoice($invoiceId);
        // Checl for installment

        // Update order
        $order->time_payment = time();
        $order->save();

        // Canonize order
        $order = $this->canonizeOrder($order);

        // Get order detail
        $detail = [];
        $where  = ['order' => $orderId];
        $select = Pi::model('detail', 'order')->select()->where($where);
        $rowset = Pi::model('detail', 'order')->selectWith($select);
        foreach ($rowset as $row) {
            $detail[$row->id] = $row->toArray();
            if (empty($row->extra)) {
                $detail[$row->id]['extra'] = [];
            } else {
                $detail[$row->id]['extra'] = json::decode($row->extra, true);
            }
            $module = $row->module;
        }

        // Update module and get back url
        $backUrl = Pi::api('order', $module)->postPaymentUpdate($order, $detail);

        // Accept Order Credit
        Pi::api('credit', 'order')->acceptOrderCredit($orderId, $invoiceId);

        // Get back url
        if (!isset($backUrl) || empty($backUrl)) {
            $backUrl = Pi::url(
                Pi::service('url')->assemble(
                    'order', [
                        'module'     => $this->getModule(),
                        'controller' => 'detail',
                        'action'     => 'index',
                        'id'         => $order->id,
                    ]
                )
            );
        }


        return $backUrl;
    }

    public function setOrderInfo($order, $completeUrl = true)
    {
        // Empty order
        if (isset($_SESSION['order'])) {
            unset($_SESSION['order']);
        }
        // Set order to session
        $_SESSION['order'] = $order;
        // Set checkout url
        if (isset($order['type_payment']) && $order['type_payment'] == 'installment') {
            $checkout = Pi::url(
                Pi::service('url')->assemble(
                    'order', [
                        'module'     => 'order',
                        'controller' => 'checkout',
                        'action'     => 'installment',
                    ]
                )
            );
        } else {
            $checkout = Pi::service('url')->assemble(
                'order', [
                    'module'     => 'order',
                    'controller' => 'checkout',
                    'action'     => 'index',
                ]
            );
            if ($completeUrl) {
                $checkout = Pi::url($checkout);
            }

        }
        return $checkout;
    }

    public function getOrderInfo()
    {
        if (isset($_SESSION['order']) && !empty($_SESSION['order'])) {
            return $_SESSION['order'];
        }
        return '';
    }

    public function updateOrderInfo($data)
    {
        if (isset($data['plan'])) {
            $_SESSION['order']['plan'] = $data['plan'];
        }
    }

    public function unsetOrderInfo()
    {
        if (isset($_SESSION['order']) && !empty($_SESSION['order'])) {
            unset($_SESSION['order']);
        }
    }

    public function cancelOrder($id)
    {
        $order = Pi::model('order', $this->getModule())->find($id);
        if ($order->uid != Pi::user()->getId()) {
            return false;
        }
        $order->status_order = \Module\Order\Model\Order::STATUS_ORDER_CANCELLED;
        $order->save();

        // Canonize order
        $order = $this->canonizeOrder($order);

        // Post cancel order
        $detail = $this->getDetail(['order' => $order['id']]);
        if (isset($detail) && !empty($detail) && isset($detail['module']) && !empty($detail['module']) && Pi::service('module')->isActive($detail['module'])) {
            Pi::api('order', $detail['module'])->postCancelUpdate($order, $detail);
        }
    }

    public function autoCancelOrder()
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());

        if (intval($config['order_auto_cancel_time']) > 0) {
            // Set where
            $where = [
                'time_create < ?' => time() - (intval($config['order_auto_cancel_time']) * 60),
                'can_pay'         => 1,
                'status_order'    => [
                    \Module\Order\Model\Order::STATUS_ORDER_CANCELLED,
                    \Module\Order\Model\Order::STATUS_ORDER_PENDING,
                ],
            ];

            $select = Pi::model('order', 'order')->select()->where($where);
            $rowset = Pi::model('detail', 'order')->selectWith($select);

            foreach ($rowset as $order) {

                // Cancel order
                $order->status_order = \Module\Order\Model\Order::STATUS_ORDER_CANCELLED;
                $order->save();

                // Canonize order
                $order = $this->canonizeOrder($order);

                // Post cancel order
                $detail = $this->getDetail(['order' => $order['id']]);
                if (isset($detail) && !empty($detail) && isset($detail['module']) && !empty($detail['module'])
                    && Pi::service('module')->isActive(
                        $detail['module']
                    )
                ) {
                    Pi::api('order', $detail['module'])->postCancelUpdate($order, $detail);
                }
            }
        }
    }

    public function getDetail($where)
    {
        $select = Pi::model('detail', 'order')->select()->where($where)->order('id DESC');
        $row    = Pi::model('detail', 'order')->selectWith($select)->current();

        return $row ? $row->toArray() : null;
    }

    public function hasValidInvoice($id)
    {
        $invoices = Pi::api('invoice', 'order')->getInvoiceFromOrder($id);
        foreach ($invoices as $invoice) {
            if ($invoice['type'] == 'CREDIT') {
                continue;
            }
            if ($invoice['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED) {
                return true;
            }
        }
        return false;
    }

    public function hasDraftInvoice($id)
    {
        $invoices = Pi::api('invoice', 'order')->getInvoiceFromOrder($id);
        foreach ($invoices as $invoice) {
            if ($invoice['type'] == 'CREDIT') {
                continue;
            }
            if ($invoice['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_DRAFT) {
                return true;
            }
        }
        return false;
    }

    public function hasPayment($id)
    {
        $orderTable              = Pi::model('order', 'order')->getTable();
        $invoiceTable            = Pi::model("invoice", 'order')->getTable();
        $invoiceInstallmentTable = Pi::model("invoice_installment", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])
            ->join(['invoice' => $invoiceTable], 'invoice.order = order.id', ['status'])
            ->join(['invoice_installment' => $invoiceInstallmentTable], 'invoice_installment.invoice = invoice.id')
            ->where(['order.id' => $id]);

        $rowset = Pi::db()->query($select);
        foreach ($rowset as $row) {
            if ($row['status'] != \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED
                && $row['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID
            ) {
                return true;
            }
        }
        return false;
    }

    public function hasUnpaidInstallment($id)
    {
        $orderTable              = Pi::model('order', 'order')->getTable();
        $invoiceTable            = Pi::model("invoice", 'order')->getTable();
        $invoiceInstallmentTable = Pi::model("invoice_installment", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])
            ->join(['invoice' => $invoiceTable], 'invoice.order = order.id', ['status'])
            ->join(['invoice_installment' => $invoiceInstallmentTable], 'invoice_installment.invoice = invoice.id')
            ->where(
                ['order.id'                           => $id, 'invoice.status != ' . \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED,
                 'invoice_installment.status_payment' => \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID]
            );

        $rowset = Pi::db()->query($select);
        return count($rowset);
    }

    public function getTimePayment($id)
    {
        $orderTable              = Pi::model('order', 'order')->getTable();
        $invoiceTable            = Pi::model("invoice", 'order')->getTable();
        $invoiceInstallmentTable = Pi::model("invoice_installment", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['order' => $orderTable])->columns([])
            ->join(['invoice' => $invoiceTable], 'invoice.order = order.id', [])
            ->join(['invoice_installment' => $invoiceInstallmentTable], 'invoice_installment.invoice = invoice.id', ['time_payment'])
            ->where(
                [
                    'order.id'       => $id,
                    'invoice.status' => \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED,
                ]
            )
            ->order(['invoice.id DESC', 'invoice_installment.id']);

        $row = Pi::db()->query($select)->current();
        if ($row) {
            return $row['time_payment'];
        }

        return 0;
    }

    public function createExtraDetailForProduct($values)
    {
        return json_encode(
            [
                'item' => $values['module_item'],
            ]
        );
    }

    public function getExtraFieldsFormForOrder()
    {
        return [];
    }

    public function isAlwaysAvailable($order)
    {
        return [
            'status' => 1,
        ];
    }

    public function showInInvoice($order, $product)
    {
        return true;
    }

    public function postCancelUpdate($order, $detail)
    {
        return true;
    }
}

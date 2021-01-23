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
 * Pi::api('invoice', 'order')->createInvoice($id);
 * Pi::api('invoice', 'order')->generatCode();
 * Pi::api('invoice', 'order')->getInvoice($id);
 * Pi::api('invoice', 'order')->getInvoiceFromOrder($order, $getLog);
 * Pi::api('invoice', 'order')->getInvoiceFromUser($uid, $compressed, $orderIds);
 * Pi::api('invoice', 'order')->getInvoiceForPayment($id);
 * Pi::api('invoice', 'order')->getIdForPayment($id);
 * Pi::api('invoice', 'order')->cancelInvoiceFromOrder($order);
 * Pi::api('invoice', 'order')->updateInvoice($randomId);
 * Pi::api('invoice', 'order')->canonizeInvoice($invoice);
 * Pi::api('invoice', 'order')->setBackUrl($id, $url);
 * Pi::api('invoice', 'order')->getInvoiceScore($uid);
 */

class Invoice extends AbstractApi
{
    /**
     * Create Invoice
     *
     * @return array
     */
    public function createInvoice($id, $uid = null, $admin = false)
    {
        // Get order
        $order = Pi::api('order', 'order')->getOrder($id);
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get user
        $uid = (is_null($uid)) ? Pi::user()->getId() : $uid;
        // Check user
        if ($config['order_anonymous'] == 0 && $uid == 0) {
            $result['status']         = 0;
            $result['pay_url']        = '';
            $result['pay_credit_url'] = '';
            $result['message']        = __('Please login for create invoice');
        } else {
            // Set invoice
            $row               = Pi::model('invoice', $this->getModule())->createRow();
            $row->code         = Pi::api('invoice', 'order')->generatCode(date('Y', $order['time_create']));
            $row->random_id    = time() + rand(100, 999);
            $row->status       = \Module\Order\Model\Invoice::STATUS_INVOICE_DRAFT;
            $row->time_create  = $order['time_create'];
            $row->time_invoice = $order['time_create'];
            $row->time_duedate = time();
            $row->order        = $order['id'];

            //$row->credit_price = 0;
            $row->gateway = $order['gateway'];
            if ($admin) {
                $row->create_by = 'ADMIN';
            }
            $row->save();

            // return array
            $result['status']         = $row->status;
            $result['order_url']      = Pi::url(
                Pi::service('url')->assemble(
                    'order',
                    [
                        'module'     => $this->getModule(),
                        'controller' => 'detail',
                        'action'     => 'index',
                        'id'         => $row->order,
                    ]
                )
            );
            $result['invoice_url']    = Pi::url(
                Pi::service('url')->assemble(
                    'order',
                    [
                        'module'     => $this->getModule(),
                        'controller' => 'invoice',
                        'action'     => 'index',
                        'id'         => $row->id,
                    ]
                )
            );
            $result['pay_url']        = Pi::url(
                Pi::service('url')->assemble(
                    'order',
                    [
                        'module'     => $this->getModule(),
                        'controller' => 'payment',
                        'action'     => 'index',
                        'id'         => $row->id,
                    ]
                )
            );
            $result['pay_credit_url'] = Pi::url(
                Pi::service('url')->assemble(
                    'order',
                    [
                        'module'     => $this->getModule(),
                        'controller' => 'payment',
                        'action'     => 'index',
                        'id'         => $row->id,
                        'credit'     => 1,
                    ]
                )
            );
            // Set invoice information on session
            if ($config['order_anonymous'] == 1 && $uid == 0) {
                $_SESSION['payment']['process']       = 1;
                $_SESSION['payment']['process_start'] = time();
                $_SESSION['payment']['invoice_id']    = $row->id;
                $_SESSION['payment']['gateway']       = $row->gateway;
            }
        }

        $result['random_id'] = $row->random_id;
        return $result;
    }

    public function generatCode($year = null)
    {
        $config = Pi::service('registry')->config->read($this->getModule());

        $year  = $year ?: date('Y');
        $count = Pi::model('invoice', 'order')->count(
            ['time_create >= ' . strtotime('01-01-' . $year) . ' AND time_create < ' . strtotime('01-01-' . ($year + 1))]
        );

        $num = $year . sprintf('%03d', ($count + 1));

        return sprintf('%s-%s', $config['invoice_code_prefix'], $num);
    }

    public function getInvoice($parameter, $type = 'id')
    {
        $invoice = Pi::model('invoice', $this->getModule())->find($parameter, $type);
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function getInvoiceFromOrder($orderId, $getLog = true)
    {
        $invoices = [];
        $where    = ['order' => $orderId];
        $select   = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset   = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $row) {
            $invoices[$row->id] = $this->canonizeInvoice($row);
        }
        return $invoices;
    }

    public function getInvoiceFromUser($uid, $compressed = false, $orderIds = [])
    {
        $invoices = [];
        // Check compressed
        if ($compressed) {
            $where = ['order.uid' => $uid, 'invoice.status' => 2, 'invoice.time_duedate < ?' => strtotime('+1 month')];
        } else {
            $where = ['order.uid' => $uid, 'invoice.status' => [1, 2]];
        }
        // Check order ids
        if (!empty($orderIds)) {
            $where['invoice.order'] = $orderIds;
        }

        $invoiceTable = Pi::model('invoice', 'order')->getTable();
        $orderTable   = Pi::model("order", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['invoice' => $invoiceTable])
            ->join(['order' => $orderTable], 'invoice.order = order.id', [])
            ->where($where);

        $rowset = Pi::db()->query($select);
        foreach ($rowset as $row) {
            $invoices[$row['id']] = $this->canonizeInvoice($row);
        }
        return $invoices;
    }

    public function getInvoiceForPayment($id)
    {
        // Set random id
        $rand = Rand::getInteger(10, 99);
        // Get invoice
        $invoice = Pi::model('invoice', $this->getModule())->find($id);
        // Update invoice
        $invoice->random_id = sprintf('%s%s', $invoice->id, $rand);
        $invoice->save();
        // Canonize invoice
        $invoice = $this->canonizeInvoice($invoice);
        return $invoice;
    }

    public function getIdForPayment($id)
    {
        // Set random id
        $rand = Rand::getInteger(10, 99);

        // Get invoice
        $invoice = Pi::model('invoice', $this->getModule())->find($id);

        // Update invoice
        $invoice->random_id = sprintf('%s%s', $invoice->id, $rand);
        $invoice->save();

        return $invoice->random_id;
    }

    public function cancelInvoiceFromOrder($order)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $where  = ['order' => $order['id']];
        $select = Pi::model('invoice', $this->getModule())->select()->where($where);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        foreach ($rowset as $invoice) {
            $invoice->status      = 0;
            $invoice->time_cancel = time();
            $invoice->save();
            // Update user credit
            if ($config['installment_credit'] && $invoice->type_payment == 'installment') {
                $message = __('Increase credit for cancel invoice');
                //Pi::api('credit', 'order')->addCredit(Pi::user()->getId(), $invoice->credit_price, 'increase', 'automatic', $message, $message);
            }
        }
    }

    public function updateInvoice($randomId, $gateway = '', $composition = [100], $dates = null, $notification = true, $paymentType = '')
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get invoice
        $invoice = Pi::model('invoice', $this->getModule())->find($randomId, 'random_id');
        $order   = Pi::api('order', 'order')->getOrder($invoice['order']);

        // Update invoice
        if (isset($paymentType) && in_array($paymentType, ['free', 'onetime', 'recurring', 'installment'])) {
            $invoice->type_payment = $paymentType;
        }
        $invoice->status = \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED;
        $invoice->save();
        $this->createInstallments($invoice->toArray(), $gateway, $dates, $composition);

        // Update user credit
        if ($config['installment_credit'] && $invoice->type_payment == 'installment') {
            $message = __('Increase credit for pay invoice');
            //Pi::api('credit', 'order')->addCredit(Pi::user()->getId(), $invoice->credit_price, 'increase', 'automatic', $message, $message);
        }
        // Canonize invoice
        $invoice = $this->canonizeInvoice($invoice);
        // Send notification
        if ($notification) {
            Pi::api('notification', 'order')->payInvoice($order, $invoice);
        }

        return $invoice;
    }

    public function canonizeInvoice($invoice)
    {
        // Check
        if (empty($invoice)) {
            return '';
        }
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set date_format
        $pattern = !empty($config['date_format']) ? $config['date_format'] : 'yyyy-MM-dd';
        // boject to array
        if (!is_array($invoice)) {
            $invoice = $invoice->toArray();
        }
        // Set time
        $invoice['time_create_view']  = _date($invoice['time_create'], ['pattern' => $pattern]);
        $invoice['time_cancel_view']  = $invoice['time_cancel'] ? _date($invoice['time_cancel'], ['pattern' => $pattern]) : __('Not canceled');
        $invoice['time_invoice']      = date('Y-m-d', $invoice['time_invoice']);
        $invoice['time_invoice_view'] = _date($invoice['time_invoice']);

        // Set url
        $invoice['order_url']      = Pi::url(
            Pi::service('url')->assemble(
                'order',
                [
                    'module'     => $this->getModule(),
                    'controller' => 'detail',
                    'action'     => 'index',
                    'id'         => $invoice['order'],
                ]
            )
        );
        $invoice['invoice_url']    = Pi::url(
            Pi::service('url')->assemble(
                'order',
                [
                    'module'     => $this->getModule(),
                    'controller' => 'detail',
                    'action'     => 'index',
                    'id'         => $invoice['order'],
                ]
            )
        );
        $invoice['pay_url']        = Pi::url(
            Pi::service('url')->assemble(
                'order',
                [
                    'module'     => $this->getModule(),
                    'controller' => 'payment',
                    'action'     => 'index',
                    'id'         => $invoice['id'],
                ]
            )
        );
        $invoice['pay_credit_url'] = Pi::url(
            Pi::service('url')->assemble(
                'order',
                [
                    'module'     => $this->getModule(),
                    'controller' => 'payment',
                    'action'     => 'index',
                    'id'         => $invoice['id'],
                    'credit'     => 1,
                ]
            )
        );
        $invoice['print_url']      = Pi::url(
            Pi::service('url')->assemble(
                'order',
                [
                    'module'     => $this->getModule(),
                    'controller' => 'invoice',
                    'action'     => 'print',
                    'id'         => $invoice['id'],
                ]
            )
        );
        // Set anonymous pay
        $invoice['anonymous_pay_url'] = Pi::url(
            Pi::service('url')->assemble(
                'order',
                [
                    'module'     => $this->getModule(),
                    'controller' => 'payment',
                    'action'     => 'index',
                    'id'         => $invoice['id'],
                    'anonymous'  => 1,
                    'token'      => 'TOKEN_KEY',
                ]
            )
        );

        // return order
        return $invoice;
    }

    public function setBackUrl($id, $url)
    {
        $invoice           = Pi::model('invoice', $this->getModule())->find($id);
        $invoice->back_url = $url;
        $invoice->save();
    }

    public function getInvoiceScore($uid)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set point
        $pointDivision = $config['score_division'];
        $pointNegative = 0;
        $pointPositive = 0;
        $pointScore    = [
            'type',
            'amount',
        ];
        // Select
        $where = ['order.uid' => $uid, 'invoice.status' => 1];

        $invoiceTable = Pi::model('invoice', 'order')->getTable();
        $orderTable   = Pi::model("order", 'order')->getTable();

        $select = Pi::db()->select();
        $select
            ->from(['invoice' => $invoiceTable])
            ->join(['order' => $orderTable], 'invoice.order = order.id', [])
            ->where($where);

        $rowset = Pi::db()->query($select);
        foreach ($rowset as $row) {
            if ($row['time_payment'] > ($row['time_duedate'] + 86400)) {

                // Negative
                $days          = number_format(($row['time_payment'] / 86400) - (($row['time_duedate'] + 86400) / 86400));
                $point         = ($days * $row['total_price']);
                $amount        = $point * $pointDivision;
                $pointNegative = $pointNegative + $amount;
            } elseif ($row['time_duedate'] > ($row['time_payment'] + 86400)) {

                // Positive
                $days          = number_format(($row['time_duedate'] / 86400) - (($row['time_payment'] + 86400) / 86400));
                $point         = ($days * $row['total_price']);
                $amount        = $point * $pointDivision;
                $pointPositive = $pointPositive + $amount;
            }
        }

        if ($pointNegative > $pointPositive) {
            // Negative
            $pointScore['type']        = 'negative';
            $pointScore['type_view']   = __('Negative score');
            $pointScore['amount']      = ($pointNegative - $pointPositive);
            $pointScore['amount_view'] = Pi::api('api', 'order')->viewPrice(($pointNegative - $pointPositive));
        } elseif ($pointPositive > $pointNegative) {
            // Positive
            $pointScore['type']        = 'positive';
            $pointScore['type_view']   = __('Positive score');
            $pointScore['amount']      = Pi::api('api', 'order')->viewPrice(($pointPositive - $pointNegative));
            $pointScore['amount_view'] = ($pointPositive - $pointNegative);
        } else {
            // Normal
            $pointScore['type']        = 'normal';
            $pointScore['type_view']   = __('Normal score');
            $pointScore['amount']      = 0;
            $pointScore['amount_view'] = Pi::api('api', 'order')->viewPrice(0);
        }

        return $pointScore;
    }

    public function pdf($id, $controls = true)
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get order

        $invoice = Pi::api('invoice', 'order')->getInvoice($id);
        $order   = Pi::api('order', 'order')->getOrder($invoice['order']);
        // Check order
        if (empty($order)) {
            return [
                'status'  => 0,
                'message' => __('The order not found.'),
            ];
        }
        // Check order is for this user
        if ($controls) {
            if ($order['uid'] != Pi::user()->getId()) {
                return [
                    'status'  => 0,
                    'message' => __('This is not your order.'),
                ];
            }
            // Check order is for this user
            if ($order['status_order'] != \Module\Order\Model\Order::STATUS_ORDER_VALIDATED) {
                return [
                    'status'  => 0,
                    'message' => __('This order not active.'),
                ];
            }
        }

        // set Products
        $options           = [
            'credit'      => $invoice['type'] == 'CREDIT',
            'invoice'     => $id,
            'time_create' => $invoice['status'] == \Module\Order\Model\Invoice::STATUS_INVOICE_CANCELLED ? $invoice['time_cancel'] : time(),
            'order'       => $order // used in guide module
        ];
        $order['products'] = Pi::api('order', 'order')->listProduct($order['id'], $options);

        foreach ($order['products'] as $key => $product) {
            if (!Pi::api('order', $product['module'])->showInInvoice($order, $product)) {
                unset($order['products'][$key]);
                continue;
            }
            $unconsumedPrice = $product['extra']['unconsumedPrice'];

            $order['total_product_price']     += $product['product_price'] - $product['discount_price'] - $unconsumedPrice;
            $order['total_shipping_price']    += $product['shipping_price'];
            $order['total_packing_price']     += $product['packing_price'];
            $order['total_setup_price']       += $product['setup_price'];
            $order['total_vat_price']         += $product['vat_price'];
            $order['total_unconsommed_price'] += $unconsumedPrice ?: 0;
        }

        $order['total_product_price_view']     = Pi::api('api', 'order')->viewPrice($order['total_product_price']);
        $order['total_shipping_price_view']    = Pi::api('api', 'order')->viewPrice($order['total_shipping_price']);
        $order['total_packing_price_view']     = Pi::api('api', 'order')->viewPrice($order['total_packing_price']);
        $order['total_setup_price_view']       = Pi::api('api', 'order')->viewPrice($order['total_setup_price']);
        $order['total_vat_price_view']         = Pi::api('api', 'order')->viewPrice($order['total_vat_price']);
        $order['total_unconsommed_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_unconsommed_price']);
        $order['total_price']                  = $order['total_product_price'] + $order['total_shipping_price'] + $order['total_packing_price']
            + $order['total_setup_price']
            + $order['total_vat_price'] - $order['total_discount_price'];

        $order['total_price_view'] = Pi::api('api', 'order')->viewPrice($order['total_price']);

        // set Products
        $order['invoice'] = $invoice;
        // set delivery information
        $order['deliveryInformation'] = '';
        if ($order['delivery'] > 0 && $order['location'] > 0) {
            $order['deliveryInformation'] = Pi::api('delivery', 'order')->getDeliveryInformation($order['location'], $order['delivery']);
        }

        $installments            = Pi::api('installment', 'order')->getInstallmentsFromInvoice($id);
        $order['status_payment'] = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID;
        foreach ($installments as $installment) {
            if ($installment['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID) {
                $order['status_payment'] = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID;
                break;
            }
        }
        if (count($installments) == 0) {
            $order['status_payment'] = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID;
        }
        if ($order['status_payment'] == \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID) {
            $installment                = current($installments);
            $order['time_payment_view'] = _date($installment['time_payment']);
        } else {
            $order['time_duedate_view'] = _date($installment['time_duedate']);
        }
        $gateways = Pi::api('gateway', 'order')->getAllGatewayList();
        $gateway  = [];
        foreach ($installments as $installment) {
            if (isset($gateways[$installment['gateway']]) && isset($gateways[$installment['gateway']]['option']['invoice_name'])
                && $gateways[$installment['gateway']]['option']['invoice_name'] != null
            ) {
                $gateway[] = $gateways[$installment['gateway']]['option']['invoice_name'];
            } else {
                $gateway[] = __($installment['gateway']);
            }
        }
        $order['gateway'] = join(', ', $gateway);

        $address  = Pi::api('orderAddress', 'order')->findOrderAddress($order['id'], 'INVOICING');
        $template = 'order:front/print';
        $isCredit = $order['invoice']['type'] == 'CREDIT' || $order['total_price'] < 0;
        $data     = ['order' => $order, 'address' => $address, 'config' => $config, 'isCredit' => $isCredit];

        $name = sprintf("%s-%s.pdf", $config['order_filename_prefix'], $invoice['code']);
        Pi::service('html2pdf')->pdf($template, $data, $name);
    }

    public function generateCreditInvoice($invoice)
    {
        $row               = Pi::model('invoice', 'order')->createRow();
        $row->code         = Pi::api('invoice', 'order')->generatCode();
        $row->random_id    = time() + rand(100, 999);
        $row->status       = \Module\Order\Model\Invoice::STATUS_INVOICE_VALIDATED;
        $row->time_create  = time();
        $row->time_invoice = time();
        $row->order        = $invoice['order'];
        $row->create_by    = 'ADMIN';
        $row->type         = 'CREDIT';
        $row->extra        = json_encode(['initial_invoice' => $invoice['code']]);
        $row->save();

        $products   = Pi::api('order', 'order')->listProduct($invoice['order']);
        $totalPrice = 0;
        foreach ($products as $product) {
            if ($product['module'] == 'order' && $product['product_type'] == 'credit') {
                continue;
            }

            $detail                 = Pi::model('detail', 'order')->createRow();
            $detail->order          = $invoice['order'];
            $detail->module         = 'order';
            $detail->product        = 0;
            $detail->product_type   = 'credit';
            $detail->product_price  = $product['product_price'];
            $detail->vat_price      = $product['vat_price'];
            $detail->setup_price    = $product['setup_price'];
            $detail->packing_price  = $product['packing_price'];
            $detail->shipping_price = $product['shipping_price'];
            $detail->discount_price = $product['discount_price'];
            $detail->time_start     = $product['time_start'];
            $detail->time_end       = $product['time_end'];

            $detail->number = 1;
            $detail->extra  = json_encode(['invoice' => $row->id]);
            $detail->save();

            $totalPrice += $product['product_price'] + $product['vat_price'] + $product['setup_price'] + $product['packing_price'] + $product['shipping_price']
                - $product['discount_price'];
        }

        $installment                 = Pi::model('invoice_installment', 'order')->createRow();
        $installment->count          = 1;
        $installment->gateway        = 'manual';
        $installment->status_payment = \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID;
        $installment->due_price      = $totalPrice;
        $installment->invoice        = $row->id;
        $installment->time_duedate   = time();
        $installment->save();
    }

    public function createInstallments($invoice, $gateway = 'manual', $dates = null, $composition = ['100'])
    {
        // Find due price
        $products = Pi::api('order', 'order')->listProduct($invoice['order']);
        $duePrice = 0;
        foreach ($products as $product) {
            $duePrice += $product['product_price'] - $product['discount_price'] + $product['shipping_price'] + $product['packing_price']
                + $product['setup_price'] + $product['vat_price'];
        }
        $count = 1;

        $total = 0;
        foreach ($composition as $key => $compose) {
            $duePriceInstallment = number_format($duePrice * $compose / 100, 2, '.', '');
            $invoiceInstallment  = Pi::model('invoice_installment', 'order')->createRow();

            $installment = [
                'invoice'        => $invoice['id'],
                'count'          => $count,
                'gateway'        => $gateway,
                'status_payment' => \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID,
                'time_payment'   => 0,
                'time_duedate'   => isset($dates[$key]) ? $dates[$key] : time(),
                'due_price'      => $key + 1 == count($composition) ? $duePrice - $total : $duePriceInstallment,
            ];

            $total += $duePriceInstallment;

            $invoiceInstallment->assign($installment);
            $invoiceInstallment->save();
            $count++;
        }
    }
}

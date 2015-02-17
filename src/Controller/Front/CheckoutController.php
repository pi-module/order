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

namespace Module\Shop\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Shop\Form\OrderForm;
use Module\Shop\Form\OrderFilter;
use Zend\Json\Json;

class CheckoutController extends ActionController
{
    /**
     * order Columns
     */
    protected $orderColumns = array(
        'id', 'uid', 'code', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'company', 'address', 
        'country', 'city', 'zip_code', 'ip', 'status_order', 'status_payment', 'status_delivery', 
        'time_create', 'time_payment', 'time_delivery', 'time_finish', 'user_note', 'admin_note', 
        'number', 'product_price', 'discount_price', 'shipping_price', 'packing_price', 
        'total_price', 'paid_price', 'packing', 'delivery', 'location','payment_method', 'payment_adapter',
    );	

    public function informationAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Set cart
        $cart = $_SESSION['shop']['cart'];
        if (empty($cart)) {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('Your cart is empty.'), 'error');
        }
        // Check order is active or inactive
        if ($this->config('order_method') == 'inactive') {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }
        // Set order form
        $form = new OrderForm('order');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new OrderFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Set just order fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->orderColumns)) {
                        unset($values[$key]);
                    }
                }
                // Get gateway
                if ($this->config('order_method') == 'both') {
                    if ($cart['invoice']['total']['payment'] == 'offline') {
                        $values['payment_adapter'] = 'Offline';
                        $values['payment_method'] = 'offline';
                    } else {
                        $gateway = Pi::api('gateway', 'payment')->getGatewayInfo($cart['invoice']['total']['payment']);
                        $values['payment_adapter'] = $gateway['path'];
                        $values['payment_method'] = $gateway['type']; 
                    }
                } elseif($this->config('order_method') == 'online') {
                    $gateway = Pi::api('gateway', 'payment')->getGatewayInfo($cart['invoice']['total']['payment']);
                    $values['payment_adapter'] = $gateway['path'];
                    $values['payment_method'] = $gateway['type'];
                } elseif($this->config('order_method') == 'offline') {
                    $values['payment_adapter'] = 'Offline';
                    $values['payment_method'] = 'offline';
                }
                // Set values
                $values['uid'] = Pi::user()->getId();
                $values['ip'] = Pi::user()->getIp();
                $values['status_order'] = 1;
                $values['status_payment'] = 1;
                $values['status_delivery'] = 1;
                $values['time_create'] = time();
                $values['number'] = $cart['invoice']['total']['number'];
                $values['product_price'] = $cart['invoice']['total']['price'];
                $values['discount_price'] = $cart['invoice']['total']['discount'];
                $values['shipping_price'] = $cart['invoice']['total']['shipping'];
                $values['packing_price'] = 0;
                $values['total_price'] = $cart['invoice']['total']['total_price'];
                $values['delivery'] = $cart['invoice']['total']['delivery'];
                $values['location'] = $cart['invoice']['total']['location'];
                $values['code'] = Pi::api('order', 'shop')->codeOrder();
                // Save values to order
                $row = $this->getModel('order')->createRow();
                $row->assign($values);
                $row->save();
                // Set user info
                Pi::api('user', 'shop')->setUserInfo($values);
                // Save order basket
                foreach ($cart['invoice']['product'] as $product) {
                    $basket = $this->getModel('order_basket')->createRow();
                    $basket->order = $row->id;
                    $basket->product = $product['id'];
                    $basket->product_price = $product['price'];
                    $basket->discount_price = $product['discount'];
                    $basket->total_price = $product['total'];
                    $basket->number = $product['number'];
                    $basket->save();
                }
                // Set payment information
                if ($row->payment_method == 'offline') {
                    $result['status'] = 1;
                    $result['message'] = __('Your order saved and we will call you soon');
                    $result['invoice_url'] = Pi::url($this->url('', array(
                        'module'        => $this->params('module'), 
                        'controller'    => 'checkout',
                        'action'        => 'finish',
                        'id'            => $row->id,
                    )));
                } else {
                    // Set invoice description
                    $description = array();
                    foreach ($cart['product'] as $product) {
                        $item = array();
                        $item['id'] = $product['id'];
                        $item['title'] = $product['title'];
                        $item['price'] = $product['price'];
                        $item['number'] = $product['number'];
                        $item['total'] = $product['total'];
                        $description[$product['id']] = $item;
                    }
                    // Set order array
                    $order = array();
                    $order['module'] = $this->getModule();
                    $order['part'] = 'order';
                    $order['id'] = $row->id;
                    $order['amount'] = $row->total_price;
                    $order['adapter'] = $row->payment_adapter;
                    $order['description'] = Json::encode($description);
                    // Payment module
                    $result = Pi::api('invoice', 'payment')->createInvoice(
                        $order['module'], 
                        $order['part'], 
                        $order['id'], 
                        $order['amount'], 
                        $order['adapter'], 
                        $order['description']
                    );
                }
                // Check it save or not
                if ($result['status']) {
                    // unset cart
                    $this->setEmpty();
                    // Go to payment
                    $this->jump($result['invoice_url'], $result['message'], 'success');
                } else {
                    $message = __('Checkout data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }   
        } else {
            $message = '';
            $user = Pi::api('user', 'shop')->getUserInfo();
            $form->setData($user);
        }
        // Set cart
        $cart = $_SESSION['shop']['cart'];
        // Set view
        $this->view()->setTemplate('checkout_information');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Checkout'));
        $this->view()->assign('message', $message);
        $this->view()->assign('cart', $cart);
    }

    public function levelAjaxAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Get config
        $config = Pi::service('registry')->config->read('shop', 'order');
        // Get info from url
        $id = $this->params('id');
        $process = $this->params('process');
        $module = $this->params('module');
        $return = array();
        $return['status'] = 0;
        $return['data'] = $_SESSION['shop']['cart']['invoice']['total'];
        $data = array();
        switch ($process) {
            case 'location':
                if ($id) {
                    // Set location
                    $location = $this->getModel('location')->find($id)->toArray();
                    $_SESSION['shop']['cart']['invoice']['total']['location'] = $location['id'];
                    $_SESSION['shop']['cart']['invoice']['total']['location_title'] = $location['title'];
                    // Get location
                    $where = array('location' => $id);
                    $select = $this->getModel('location_delivery')->select()->where($where);
                    $rowset = $this->getModel('location_delivery')->selectWith($select);
                    foreach ($rowset as $row) {
                        $delivery = $this->getModel('delivery')->find($row->delivery)->toArray();
                        if($delivery['status']) {
                            $data[$row->id] = $row->toArray();
                            $data[$row->id]['title'] = $delivery['title'];
                            $data[$row->id]['status'] = $delivery['status'];
                        }
                    }
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = $data;
                    $return['location'] = $location['title'];
                }
                break;

            case 'delivery':
                if ($id) {
                    // Set delivery
                    $delivery = $this->getModel('delivery')->find($id)->toArray();
                    $_SESSION['shop']['cart']['invoice']['total']['delivery'] = $delivery['id'];
                    $_SESSION['shop']['cart']['invoice']['total']['delivery_title'] = $delivery['title'];
                    // Get location_delivery
                    $location = $_SESSION['shop']['cart']['invoice']['total']['location'];
                    $where = array('location' => $location, 'delivery' => $id);
                    $select = $this->getModel('location_delivery')->select()->where($where)->limit(1);
                    $row = $this->getModel('location_delivery')->selectWith($select)->current();
                    // Set shipping price
                    $_SESSION['shop']['cart']['invoice']['total']['shipping'] = $row->price;
                    // Get delivery_payment
                    $where = array('delivery' => $id);
                    $select = $this->getModel('delivery_payment')->select()->where($where);
                    $rowset = $this->getModel('delivery_payment')->selectWith($select);
                    foreach ($rowset as $row) {
                        if ($row->payment == 'offline') {
                            $data['payment'][$row->id]['title'] = 'Offline';
                            $data['payment'][$row->id]['path'] = 'offline';
                            $data['payment'][$row->id]['status'] = 1;
                        } else {
                            $payment = Pi::api('gateway', 'payment')->getGatewayInfo($row->payment);
                            if($payment['status']) {
                                $data['payment'][$row->id]['title'] = $payment['title'];
                                $data['payment'][$row->id]['path'] = $payment['path'];
                                $data['payment'][$row->id]['status'] = $payment['status'];
                            }  
                        }
                    }
                    // Set Invoice
                    $invoice = $this->setInvoice();
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = $data;
                    $return['data']['shipping'] = $invoice['total']['shipping'];
                    $return['data']['total'] = $invoice['total']['total_price'];
                    $return['delivery'] = $delivery['title'];
                    $return['payment'] = ($config['order_method'] == 'offline') ? __('Offline') : '';
                }
                break; 

            case 'payment':  
                if ($id) {
                    // Set delivery
                    $_SESSION['shop']['cart']['invoice']['total']['payment'] = $id;
                    $_SESSION['shop']['cart']['invoice']['total']['payment_title'] = $id;
                    // Set return
                    $data = array(
                        'location' => $_SESSION['shop']['cart']['invoice']['total']['location_title'],
                        'delivery' => $_SESSION['shop']['cart']['invoice']['total']['delivery_title'],
                        'payment' => $_SESSION['shop']['cart']['invoice']['total']['payment_title'],
                    );
                    // Set return
                    $return['status'] = 1;
                    $return['data'] = $data;
                }
                break;   
        }
        // return
        return $return;
    }

    public function basketAjaxAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Set cart
        $cart = $_SESSION['shop']['cart'];
        // Get info from url
        $process = $this->params('process');
        $product = $this->params('product');
        $module = $this->params('module');
        // Set return
        $return = array();
        $return['message'] = __('Please select product');
        $return['id'] = $product;
        $return['ajaxStatus'] = 0;
        $return['actionStatus'] = 0;
        $return['actionName'] = $process;
        // process
        switch ($process) {
        	case 'remove':
                unset($cart['product'][$product]);
        	    $return['message'] = __('Selected product removed from your cart');
                $return['ajaxStatus'] = 1;
                $return['actionStatus'] = 1;
        		break;

        	case 'number':
        	    $number = $cart['product'][$product]['number'];
        	    if ($number > 0) {
        	    	$getNumber = $this->params('number');
        	    	$newNumber = $number + $getNumber;
        	    	if ($newNumber > 0) {
                        $newTotal = $newNumber * $cart['product'][$product]['price'];
        	    		$cart['product'][$product]['number'] = $newNumber;
                        $cart['product'][$product]['total'] = $newTotal;
        	    		$return['message'] = __('Update number');
                        $return['actionNumber'] = $newNumber;
                        $return['ajaxStatus'] = 1;
                        $return['actionStatus'] = 1;
                        $return['actionTotal'] = $newTotal;
        	    	} else {
        	    		$return['message'] = __('You can not set product number to 0');
                        $return['ajaxStatus'] = 1;
                        $return['actionStatus'] = 0;
        	    	}
        	    }
        		break;
        }
        // Set session
        $_SESSION['shop']['cart'] = $cart;
        // Set Invoice
        $this->setInvoice();
        // return
        return $return;
    }

    public function cartAjaxAction()
    {
        // Set Invoice
        $this->setInvoice();
        // Set cart
        $invoice = $_SESSION['shop']['cart']['invoice']['total'];
        // Set data
        $data = array();
        $data['price'] = $invoice['price'];
        $data['discount'] = $invoice['discount'];
        $data['number'] = $invoice['number'];
        $data['total'] = $invoice['total_price'];
        // Set return
        $return = array();
        $return['status'] = 1;
        $return['data'] = $data;
        return $return;
    }

    public function addAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Check order is active or inactive
        if ($this->config('order_method') == 'inactive') {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }
        // Get info from url
        $slug = $this->params('slug');
        $module = $this->params('module');
        // Set view
        $this->view()->setTemplate(false);
        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Find product
        $product = $this->getModel('product')->find($slug, 'slug');
        $product = Pi::api('product', 'shop')->canonizeProductLight($product);
        // Check product
        if (!$product['marketable']) {
        	$url = array('', 'module' => $module, 'controller' => 'index');
            $this->jump($url, __('The product was not marketable.'), 'error');
        } else {
            // Set total price
            $this->setProduct($product);
            // Set Invoice
            $this->setInvoice();
            // Go to cart
            $url = array('', 'module' => $module, 'controller' => 'checkout', 'action' => 'cart');
            return $this->redirect()->toRoute('', $url);
        }
    }

    public function cartAction()
    {
    	// Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Check order is active or inactive
        if ($this->config('order_method') == 'inactive') {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index');
            $this->jump($url, __('So sorry, At this moment order is inactive'), 'error');
        }
        // Set Invoice
        $this->setInvoice();
    	$cart = $_SESSION['shop']['cart'];
    	if (empty($cart['product'])) {
            $module = $this->params('module');
        	$url = array('', 'module' => $module, 'controller' => 'index');
            $this->jump($url, __('Your cart are empty.'), 'error');
    	}
    	$this->view()->setTemplate('checkout_cart');
    	$this->view()->assign('cart', $cart);
    }

    public function emptyAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Set empty
        $this->setEmpty();
        // Back
        $module = $this->params('module');
        $url = array('', 'module' => $module, 'controller' => 'index');
        $this->jump($url, __('Your cart are empty'), 'success');
    }

    public function finishAction()
    {
        // Check user is login or not
        Pi::service('authentication')->requireLogin();
        // Check order is active or inactive
        if ($this->config('order_method') == 'inactive') {
            $url = array('', 'module' => $this->params('module'), 'controller' => 'index', 'action' => 'index');
            $this->jump($url, __('So sorry, At this moment order is inactive'));
        }
        // Get info from url
        $id = $this->params('id');
        $module = $this->params('module');
        // Find order
        $order = $this->getModel('order')->find($id);
        // Check order
        if (!$order->id) {
            $url = array('', 'module' => $module, 'controller' => 'index', 'action' => 'index');
            $this->jump($url, __('Order not set.'));
        }
        // Check user
        if ($order->uid != Pi::user()->getId()) {
            $url = array('', 'module' => $module, 'controller' => 'index', 'action' => 'index');
            $this->jump($url, __('It not your order.'));
        }
        // Check status payment
        if ($order->payment_method == 'online' && $order->status_payment != 2) {
            $url = array('', 'module' => $module, 'controller' => 'index', 'action' => 'index');
            $this->jump($url, __('This order not pay'));
        }
        // Check time payment
        $time = time() - 3600;
        if ($order->payment_method == 'online') {
            if ($time > $order->time_payment) {
                $url = array('', 'module' => $module, 'controller' => 'index', 'action' => 'index');
                $this->jump($url, __('This is old order and you pay it before'));
            }
        } else {
            if ($time > $order->time_create) {
                $url = array('', 'module' => $module, 'controller' => 'index', 'action' => 'index');
                $this->jump($url, __('This is old order and you pay it before'));
            }
        }
        // canonize Order
        $order = Pi::api('order', 'shop')->canonizeOrder($order);
        // Set links
        $order['order_link'] = Pi::url($this->url('', array(
            'module'      => $module, 
            'controller'  => 'user', 
            'action'      => 'order', 
            'id'          => $order['id']
        )));
        $order['user_link'] = Pi::url($this->url('', array(
            'module'      => $module, 
            'controller'  => 'user'
        )));
        $order['index_link'] = Pi::url($this->url('', array(
            'module'      => $module, 
            'controller'  => 'index'
        )));
        // Get invoice information
        Pi::service('i18n')->load(array('module/payment', 'default'));
        $invoice = Pi::api('invoice', 'payment')->getInvoiceFromItem('shop', 'order', $order['id']);
        // Send Mail
        Pi::api('order', 'shop')->sendUserMail($order);
        Pi::api('order', 'shop')->sendAdminMail($order);
        // Set view
        $this->view()->setTemplate('checkout_finish');
        $this->view()->assign('order', $order);
        $this->view()->assign('invoice', $invoice);
    }

    protected function setEmpty()
    {
        if (isset($_SESSION['shop']['cart'])) {
            unset($_SESSION['shop']['cart']);
        }
    }

    protected function setInvoice()
    {    
    	$invoice = array();
        $invoice['total']['price'] = 0;
        $invoice['total']['number'] = 0;
        $invoice['total']['discount'] = 0;
        $invoice['total']['shipping'] = 0;
        // Get cart
    	$cart = $_SESSION['shop']['cart'];
        // Set invoice product
    	foreach ($cart['product'] as $product) {
    		// Set product item
            $item = array();
            $item['id'] = $product['id'];
            $item['number'] = $product['number'];
            $item['price'] = $product['price'];
            $item['price_view'] = Pi::api('product', 'shop')->viewPrice($item['price']);
            $item['discount'] = 0;
            $item['total'] = ($product['number'] * $product['price']);
            $item['total_view'] = Pi::api('product', 'shop')->viewPrice($item['total']);
            $invoice['product'][$product['id']] = $item;
            // Set total
    		$invoice['total']['price'] = $invoice['total']['price'] + ($product['price'] * $product['number']);
    		$invoice['total']['number'] = $invoice['total']['number'] + $product['number'];
    		$invoice['total']['discount'] = $invoice['total']['discount'] + 0;
            // Update cart product
            $cart['product'][$item['id']]['total'] = $item['total'];
            $cart['product'][$item['id']]['total_view'] = $item['total_view'];
    	}
        // Set price
        $invoice['total']['location'] = (isset($cart['invoice']['total']['location'])) ? $cart['invoice']['total']['location'] : 0;
        $invoice['total']['delivery'] = (isset($cart['invoice']['total']['delivery'])) ? $cart['invoice']['total']['delivery'] : 0;
        $invoice['total']['shipping'] = (isset($cart['invoice']['total']['shipping'])) ? intval($cart['invoice']['total']['shipping']) : 0;
        $invoice['total']['payment'] = (isset($cart['invoice']['total']['payment'])) ? $cart['invoice']['total']['payment'] : 'offline';
        $invoice['total']['location_title'] = (isset($cart['invoice']['total']['location_title'])) ? $cart['invoice']['total']['location_title'] : '';
        $invoice['total']['delivery_title'] = (isset($cart['invoice']['total']['delivery_title'])) ? $cart['invoice']['total']['delivery_title'] : '';
        $invoice['total']['payment_title'] = (isset($cart['invoice']['total']['payment_title'])) ? $cart['invoice']['total']['payment_title'] : __('Offline');
        $invoice['total']['total_price'] = intval($invoice['total']['price'] - $invoice['total']['discount']) + $invoice['total']['shipping'];
        $invoice['total']['price_view'] = Pi::api('product', 'shop')->viewPrice($invoice['total']['price']);
    	$invoice['total']['discount_view'] = Pi::api('product', 'shop')->viewPrice($invoice['total']['discount']);
        $invoice['total']['shipping_view'] = Pi::api('product', 'shop')->viewPrice($invoice['total']['shipping']);
    	$invoice['total']['total_price_view'] = Pi::api('product', 'shop')->viewPrice($invoice['total']['total_price']);
    	// Set Seaaion
    	$cart['invoice'] = $invoice;
        $_SESSION['shop']['cart'] = $cart;
        // return invoice
        return $invoice;
    }

    protected function setProduct($product)
    {
        $cart = $_SESSION['shop']['cart'];
        // Set number
        if (isset($cart['product'][$product['id']]) 
            && !empty($cart['product'][$product['id']])) 
        {
            if (isset($cart['product'][$product['id']]['number']) 
                && !empty($cart['product'][$product['id']]['number'])) 
            {
                $product['number'] = $cart['product'][$product['id']]['number'] + 1;
            } else {
                $product['number'] = 1;
            }
        } else {
            $product['number'] = 1;
        }
        // Set total price
        $product['total'] = intval(($product['price'] * $product['number']));
        $product['total_view'] = Pi::api('product', 'shop')->viewPrice($product['total']);
        // Set session
        $cart['product'][$product['id']] = $product;
        $_SESSION['shop']['cart'] = $cart;
    }
}
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

namespace Module\Order\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Json\Json;

/*
 * Pi::api('user', 'order')->getPaymentHistory($user);
 */

class User extends AbstractApi
{
	public function getPaymentHistory($user = '', $module = '')
	{
		// Get user id if not set
		if (empty($user)) {
			$user = Pi::user()->getId();
		}
		// Check user id
		if (!$user || $user == 0) {
			return array();
		}
		// Get user info
		$userInfo = Pi::user()->get($user, array('id', 'identity', 'name', 'email'));
		// Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get info
        $list = array();
        $order = array('id DESC', 'time_create DESC');
        $where = array('uid' => $user);
        //
        /* if (!$config['payment_shownotpay']) {
            $where['status'] = 1;
        } */
        // 
        if (!empty($module)) {
        	$where['module'] = $module;
        }
        $select = Pi::model('invoice', $this->getModule())->select()->where($where)->order($order);
        $rowset = Pi::model('invoice', $this->getModule())->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['description'] = Json::decode($list[$row->id]['description'], true);
            $list[$row->id]['user'] = $userInfo;
            $list[$row->id]['time_create_view'] = _date($list[$row->id]['time_create']);
            $list[$row->id]['time_payment_view'] = ($list[$row->id]['time_payment']) ? _date($list[$row->id]['time_payment']) : '';
            $list[$row->id]['amount_view'] = _currency($list[$row->id]['amount']);
            $list[$row->id]['invoice_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                'module'        => $this->getModule(),
                'action'        => 'invoice',
                'id'            => $row->id,
            )));
            $list[$row->id]['pay_url'] = Pi::url(Pi::service('url')->assemble('order', array(
                'module'        => $this->getModule(),
                'action'        => 'pay',
                'id'            => $row->id,
            )));
        }
        // return
        return $list;
	}
}	
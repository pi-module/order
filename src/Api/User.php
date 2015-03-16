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
 * Pi::api('user', 'order')->getUserInformation($user);
 * Pi::api('user', 'order')->getPaymentHistory($user);
 */

class User extends AbstractApi
{
	public function getUserInformation($user = '')
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
        $user = Pi::user()->get($user, array(
            'id', 'identity', 'name', 'email', 'first_name', 'last_name', 'id_number', 'phone', 'mobile', 'credit', 
            'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company', 'company_id', 'company_vat', 
        ));
        // Check user first_name
        if (!isset($user['first_name'])) {
            $user['first_name'] = '';
        }
        // Check user last_name
        if (!isset($user['last_name'])) {
            $user['last_name'] = '';
        }
        // Check user id_number
        if (!isset($user['id_number'])) {
            $user['id_number'] = '';
        }
        // Check userphone
        if (!isset($user['phone'])) {
            $user['phone'] = '';
        }
        // Check usermobile
        if (!isset($user['mobile'])) {
            $user['mobile'] = '';
        }
        // Check useraddress1
        if (!isset($user['address1'])) {
            $user['address1'] = '';
        }
        // Check useraddress2
        if (!isset($user['address2'])) {
            $user['address2'] = '';
        }
        // Check usercountry
        if (!isset($user['country'])) {
            $user['country'] = '';
        }
        // Check userstate
        if (!isset($user['state'])) {
            $user['state'] = '';
        }
        // Check usercity
        if (!isset($user['city'])) {
            $user['city'] = '';
        }
        // Check userzip_code
        if (!isset($user['zip_code'])) {
            $user['zip_code'] = '';
        }
        // Check usercompany
        if (!isset($user['company'])) {
            $user['company'] = '';
        }
        // Check usercompany_id
        if (!isset($user['company_id'])) {
            $user['company_id'] = '';
        }
        // Check usercompany_vat
        if (!isset($user['company_vat'])) {
            $user['company_vat'] = '';
        }
        return $user;
    }

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
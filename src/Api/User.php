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

/*
 * Pi::api('user', 'order')->getUserInformation($uid, $type);
 * Pi::api('user', 'order')->updateUserInformation($user, $uid);
 */

class User extends AbstractApi
{
    public function getUserInformation($uid = '', $type = 'normal')
    {
        // Get user id if not set
        if (empty($uid)) {
            $uid = Pi::user()->getId();
        }
        // Check user id
        if (!$uid || $uid == 0) {
            return [];
        }
        // Check type
        switch ($type) {
            case 'normal':
                $fields = [
                    'id', 'identity', 'name', 'email', 'first_name', 'last_name', 'id_number', 'phone', 'mobile', 'credit',
                    'address1', 'address2', 'country', 'state', 'city', 'zip_code', 'company', 'company_id', 'company_vat',
                ];
                break;

            case 'light':
                $fields = [
                    'id', 'identity', 'name', 'email',
                ];
                break;
        }
        // Get user info
        $user = Pi::user()->get($uid, $fields);
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

    public function updateUserInformation($user, $uid = '')
    {
        // Set uid
        if (empty($uid)) {
            if (isset($user['uid']) && !empty($user['uid'])) {
                $uid = $user['uid'];
            } else {
                $uid = Pi::user()->getId();
            }
        }
        // Fields
        $fields = Pi::api('user', 'user')->getFields($uid, 'profile');
        // Set just needed fields
        foreach (array_keys($user) as $key) {
            if (!in_array($key, array_keys($fields))) {
                unset($user[$key]);
            }
        }
        // From user module
        $user['last_modified'] = time();
        $status                = Pi::api('user', 'user')->updateUser($uid, $user);
        if ($status == 1) {
            Pi::service('event')->trigger('user_update', $uid);
        }
    }
}

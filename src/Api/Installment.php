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

/*
 * Pi::api('installment', 'order')->planList();
 * Pi::api('installment', 'order')->setPriceForInvoice($price, $plan, $user);
 * Pi::api('installment', 'order')->setPriceForView($price, $user);
 */

class Installment extends AbstractApi
{
    public function planList()
    {
    	// Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Set list
        $list = array();
    	// Plan 0
        $list[0] = array(
            'id'          => 0,
            'title'       => __('One time'),
            'prepayment'  => '100',
            'profit'      => '0',
            'total'       => '0',
        );
        // Plan 1
        $list[1] = array(
            'id'          => 1,
            'title'       => $config['plan_1_title'],
            'prepayment'  => $config['plan_1_prepayment'],
            'profit'      => $config['plan_1_profit'],
            'total'       => $config['plan_1_total'],
        );
    	// Plan 2
        $list[2] = array(
            'id'          => 2,
            'title'       => $config['plan_2_title'],
            'prepayment'  => $config['plan_2_prepayment'],
            'profit'      => $config['plan_2_profit'],
            'total'       => $config['plan_2_total'],
        );
        // Plan 3
        $list[3] = array(
            'id'          => 3,
            'title'       => $config['plan_3_title'],
            'prepayment'  => $config['plan_3_prepayment'],
            'profit'      => $config['plan_3_profit'],
            'total'       => $config['plan_3_total'],
        );
        // Plan 4
        $list[4] = array(
            'id'          => 4,
            'title'       => $config['plan_4_title'],
            'prepayment'  => $config['plan_4_prepayment'],
            'profit'      => $config['plan_4_profit'],
            'total'       => $config['plan_4_total'],
        );
        // Plan 5
        $list[5] = array(
            'id'          => 5,
            'title'       => $config['plan_5_title'],
            'prepayment'  => $config['plan_5_prepayment'],
            'profit'      => $config['plan_5_profit'],
            'total'       => $config['plan_5_total'],
        );
    	return $list;
    }

    public function setPriceForInvoice($price, $plan, $user = array())
    {
        // Get config
        $config = Pi::service('registry')->config->read($this->getModule());
        // Get user
        if (empty($user) && $config['installment_credit']) {
            $user = Pi::api('user', 'order')->getUserInformation();
        }
        // Get plan
    	$planList = $this->planList();
    	$planList = $planList[$plan];
        // Set price
        $prepaymentPrice = ($price / 100) * $planList['prepayment'];
        $remainingPrice = $price - $prepaymentPrice;
        $step1 = $remainingPrice * ($planList['profit'] / 100);
        $step2 = $step1 * $planList['total'];
        $step3 = $step2 + $remainingPrice;
        // Check total
        if ($planList['total'] > 0) {
            $installmentPrice = $step3 / $planList['total'];
        } else {
            $installmentPrice = $step3;
        }
        // Set credit
        $credit = 0;
        if ($remainingPrice > 0) {
            $credit = $remainingPrice / $planList['total'];
        }
        // Set prepayment invoices
        $invoices = array();
        $invoices[0] = array(
            'price'   => Pi::api('api', 'order')->makePrice($prepaymentPrice),
            'duedate' => time(),
            'credit'  => 0,
        );
        $total = $prepaymentPrice;
        // Set all other invoices
        for ($i=1; $i <= $planList['total']; $i++) {
            // Set price
            $price = Pi::api('api', 'order')->makePrice($installmentPrice);
            // Set array
            $invoices[$i] = array(
                'price'   => $price,
                'duedate' => $this->makeTime($i),
                'credit'  => $credit,
            );
            // Set total
            $total = $total + $installmentPrice;
        }
        // Check allow
        $allowed = 1;
        if ($config['installment_credit']) {
            if ($remainingPrice > $user['credit']) {
                $allowed = 0;
            }
        }
        // Set total
        $invoices['total'] = array(
            'price'        => $total,
            'duedate'      => '',
            'allowed'      => $allowed,
            'installment'  => $remainingPrice,
        );
        return $invoices;
    }

    public function setPriceForView($price, $user = array())
    {
    	// Get plan
    	$plans = $this->planList();
    	// Set plans
    	$list = array();
    	foreach ($plans as $plan) {
    		$list[$plan['id']] = array(
    			'id'          => $plan['id'],
        		'title'       => $plan['title'],
        		'prepayment'  => $plan['prepayment'],
        		'profit'      => $plan['profit'],
        		'total'       => $plan['total'],
        		'invoices'    => $this->setPriceForInvoice($price, $plan['id'], $user),
    		);
    	}
    	return $list;
    }

    public function makeTime($i)
    {
        switch (Pi::config('date_calendar')) {
            // Set for Iran time
            case 'persian':
                require_once Pi::path('module') . '/order/src/Api/pdate.php';
                // Set day
                if (in_array(pdate('d'), array('01','02','03','04','05','06','07','08','09','10'))) {
                    $day = 10;
                } elseif (in_array(pdate('d'), array('11','12','13','14','15','16','17','18','19','20'))) {
                    $day = 20;
                } elseif (in_array(pdate('d'), array('21','22','23','24','25','26','27','28','29','30','31'))) {
                    if (pdate('m') == 12) {
                        $day = 29;
                    } else {
                        $day = 30;
                    }
                }
                // make time
                $month = pdate('m') + $i;
                $year = pdate('Y');
                if ($month > 12) {
                    $month = $month - 12;
                    $year = $year + 1;
                }
                $time = pmktime(0, 0, 0, $month, $day, $year);
                break;
                
            default:
                $time = strtotime(sprintf('+%s month', $i));
                break;
        }

        return $time;
    }
}
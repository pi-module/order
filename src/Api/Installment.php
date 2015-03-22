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
 * Pi::api('installment', 'order')->setPriceForInvoice($price, $plan);
 * Pi::api('installment', 'order')->setPriceForView($price);
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

    public function setPriceForInvoice($price, $plan)
    {
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
        // Set prepayment invoices
        $invoices = array();
        $invoices[0] = array(
            'price'    => $prepaymentPrice,
            'duedate'  => time(),
            'b'        => date('Y-m-d'),
        );
        $total = $prepaymentPrice;
        // Set all other invoices
        for ($i=1; $i <= $planList['total']; $i++) {
            $invoices[$i] = array(
                'price'    => $installmentPrice,
                'duedate'  => strtotime(sprintf('+%s month', $i)),
                'b'        => date('Y-m-d', strtotime(sprintf('+%s month', $i))),
            );
            $total = $total + $installmentPrice;
        }
        // Set total
        $invoices['total'] = array(
            'price'    => $total,
            'duedate'  => '',
            'b'        => '',
        );
        return $invoices;
    }

    public function setPriceForView($price)
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
        		'invoices'    => $this->setPriceForInvoice($price, $plan['id']),
    		);
    	}
    	return $list;
    }
}
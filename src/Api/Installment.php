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
    	$list = array();
    	// Plan 1
    	$list[1] = array(
        	'id'          => 1,
        	'title'       => __('2 months'),
        	'prepayment'  => '20',
        	'profit'      => '0',
        	'total'       => '2',
    	);
    	// Plan 2
    	$list[2] = array(
    		'id'          => 2,
        	'title'       => __('3 months'),
        	'prepayment'  => '20',
        	'profit'      => '1',
        	'total'       => '3',
    	);
    	// Plan 3
    	$list[3] = array(
    		'id'          => 3,
        	'title'       => __('4 months'),
        	'prepayment'  => '20',
        	'profit'      => '2',
        	'total'       => '4',
    	);
    	// Plan 4
    	$list[4] = array(
    		'id'          => 4,
        	'title'       => __('5 months'),
        	'prepayment'  => '20',
        	'profit'      => '3',
        	'total'       => '5',
    	);
    	// Plan 5
    	$list[5] = array(
    		'id'          => 5,
        	'title'       => __('6 months'),
        	'prepayment'  => '20',
        	'profit'      => '4',
        	'total'       => '6',
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
        $installmentPrice = $step3 / $planList['total'];
        // Set prepayment invoices
        $invoices = array();
        $invoices[0] = array(
            'price'    => $prepaymentPrice,
            'duedate'  => time(),
            'b'        => date('Y-m-d'),
        );
        // Set all other invoices
        for ($i=1; $i <= $planList['total']; $i++) {
            $invoices[$i] = array(
                'price'    => $installmentPrice,
                'duedate'  => strtotime(sprintf('+%s month', $i)),
                'b'        => date('Y-m-d', strtotime(sprintf('+%s month', $i))),
            );
        }
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
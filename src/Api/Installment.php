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
            'title'       => __('1 months'),
            'prepayment'  => '20',
            'profit'      => '0',
            'total'       => '1',
        );
    	// Plan 2
    	$list[2] = array(
    		'id'          => 2,
        	'title'       => __('2 months'),
        	'prepayment'  => '20',
        	'profit'      => '1.5',
        	'total'       => '2',
    	);
    	// Plan 3
    	$list[3] = array(
    		'id'          => 3,
        	'title'       => __('3 months'),
        	'prepayment'  => '20',
        	'profit'      => '1.6',
        	'total'       => '3',
    	);
    	// Plan 4
    	$list[4] = array(
    		'id'          => 4,
        	'title'       => __('4 months'),
        	'prepayment'  => '20',
        	'profit'      => '1.7',
        	'total'       => '4',
    	);
    	// Plan 5
        $list[5] = array(
            'id'          => 5,
            'title'       => __('5 months'),
            'prepayment'  => '20',
            'profit'      => '1.8',
            'total'       => '5',
        );
        // Plan 6
        $list[6] = array(
            'id'          => 6,
            'title'       => __('6 months'),
            'prepayment'  => '20',
            'profit'      => '1.9',
            'total'       => '6',
        );
        // Plan 7
        $list[7] = array(
            'id'          => 7,
            'title'       => __('7 months'),
            'prepayment'  => '20',
            'profit'      => '2',
            'total'       => '7',
        );
        // Plan 8
        $list[8] = array(
            'id'          => 8,
            'title'       => __('8 months'),
            'prepayment'  => '20',
            'profit'      => '2.1',
            'total'       => '8',
        );
        // Plan 9
        $list[9] = array(
            'id'          => 9,
            'title'       => __('9 months'),
            'prepayment'  => '20',
            'profit'      => '2.5',
            'total'       => '9',
        );
        // Plan 10
        $list[10] = array(
            'id'          => 10,
            'title'       => __('10 months'),
            'prepayment'  => '20',
            'profit'      => '2.9',
            'total'       => '10',
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
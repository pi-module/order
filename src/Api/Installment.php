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
 */

class Installment extends AbstractApi
{
    public function planList()
    {
    	$list = array();
    	// Plan 1
    	$list[1] = array(
        	'title'       => __('2 months'),
        	'prepayment'  => '30',
        	'profit'      => '0',
        	'total'       => '2',
    	);
    	// Plan 2
    	$list[2] = array(
        	'title'       => __('3 months'),
        	'prepayment'  => '25',
        	'profit'      => '2',
        	'total'       => '3',
    	);
    	// Plan 3
    	$list[3] = array(
        	'title'       => __('4 months'),
        	'prepayment'  => '20',
        	'profit'      => '4',
        	'total'       => '4',
    	);
    	// Plan 4
    	$list[4] = array(
        	'title'       => __('5 months'),
        	'prepayment'  => '15',
        	'profit'      => '6',
        	'total'       => '5',
    	);
    	// Plan 5
    	$list[5] = array(
        	'title'       => __('6 months'),
        	'prepayment'  => '15',
        	'profit'      => '8',
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
        $installmentPrice = ((($price - $prepaymentPrice) / $planList['total']) * (100 + $planList['profit'])) / 100;
        // Set prepayment invoices
        $invoices = array();
        $invoices[0] = array(
            'price'    => $prepaymentPrice,
            'duedate'  => time(),
        );
        // Set all other invoices
        for ($i=1; $i <= $planList['total']; $i++) {
            $invoices[$i] = array(
                'price'    => $installmentPrice,
                'duedate'  => strtotime(sprintf('+%s month', $i)),
            );
        }
        return $invoices;
    }
}
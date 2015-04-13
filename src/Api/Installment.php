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
 * Pi::api('installment', 'order')->blockTable($user);
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
        if ($config['plan_1_show']) {
            $list[1] = array(
                'id'          => 1,
                'title'       => $config['plan_1_title'],
                'prepayment'  => $config['plan_1_prepayment'],
                'profit'      => $config['plan_1_profit'],
                'total'       => $config['plan_1_total'],
            );
        }
    	// Plan 2
        if ($config['plan_2_show']) {
            $list[2] = array(
                'id'          => 2,
                'title'       => $config['plan_2_title'],
                'prepayment'  => $config['plan_2_prepayment'],
                'profit'      => $config['plan_2_profit'],
                'total'       => $config['plan_2_total'],
            );
        }
        // Plan 3
        if ($config['plan_3_show']) {
            $list[3] = array(
                'id'          => 3,
                'title'       => $config['plan_3_title'],
                'prepayment'  => $config['plan_3_prepayment'],
                'profit'      => $config['plan_3_profit'],
                'total'       => $config['plan_3_total'],
            );
        }
        // Plan 4
        if ($config['plan_4_show']) {
            $list[4] = array(
                'id'          => 4,
                'title'       => $config['plan_4_title'],
                'prepayment'  => $config['plan_4_prepayment'],
                'profit'      => $config['plan_4_profit'],
                'total'       => $config['plan_4_total'],
            );
        }
        // Plan 5
        if ($config['plan_5_show']) {
            $list[5] = array(
                'id'          => 5,
                'title'       => $config['plan_5_title'],
                'prepayment'  => $config['plan_5_prepayment'],
                'profit'      => $config['plan_5_profit'],
                'total'       => $config['plan_5_total'],
            );
        }
        // Plan 6
        if ($config['plan_6_show']) {
            $list[6] = array(
                'id'          => 6,
                'title'       => $config['plan_6_title'],
                'prepayment'  => $config['plan_6_prepayment'],
                'profit'      => $config['plan_6_profit'],
                'total'       => $config['plan_6_total'],
            );
        }
        // Plan 7
        if ($config['plan_7_show']) {
            $list[7] = array(
                'id'          => 7,
                'title'       => $config['plan_7_title'],
                'prepayment'  => $config['plan_7_prepayment'],
                'profit'      => $config['plan_7_profit'],
                'total'       => $config['plan_7_total'],
            );
        }
        // Plan 8
        if ($config['plan_8_show']) {
            $list[8] = array(
                'id'          => 8,
                'title'       => $config['plan_8_title'],
                'prepayment'  => $config['plan_8_prepayment'],
                'profit'      => $config['plan_8_profit'],
                'total'       => $config['plan_8_total'],
            );
        }
        // Plan 9
        if ($config['plan_9_show']) {
            $list[9] = array(
                'id'          => 9,
                'title'       => $config['plan_9_title'],
                'prepayment'  => $config['plan_9_prepayment'],
                'profit'      => $config['plan_9_profit'],
                'total'       => $config['plan_9_total'],
            );
        }
        // Plan 10
        if ($config['plan_10_show']) {
            $list[10] = array(
                'id'          => 10,
                'title'       => $config['plan_10_title'],
                'prepayment'  => $config['plan_10_prepayment'],
                'profit'      => $config['plan_10_profit'],
                'total'       => $config['plan_10_total'],
            );
        }
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
                        $day = 28;
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

    public function blockTable($user)
    {
        require_once Pi::path('module') . '/order/src/Api/pdate.php';

        $invoices = Pi::api('invoice', 'order')->getInvoiceFromUser($user['uid']);
        
        $d = array();
        $d['all']['10-sun'] = 0;
        $d['all']['20-sun'] = 0;
        $d['all']['30-sun'] = 0;

        $month = pdate('m', strtotime('now'));
        $year = pdate('Y', strtotime('now'));

        /* Line 1 */
        $d['m']['10'] = pmktime(0, 0, 0, $month, 10, $year);
        $d['m']['10-view'] = _date(pmktime(0, 0, 0, $month, 10, $year), array('pattern' => 'yyyy/MM/dd'));
        $d['m']['10-sun'] = 0;
        $d['m']['10-invoice'] = array();
        foreach ($invoices as $invoice) {
            if ($invoice['time_duedate'] < pmktime(0, 0, 0, $month, 10, $year)) {
                $d['m']['10-invoice'][$invoice['id']] = $invoice;
                $d['m']['10-sun'] = $d['m']['10-sun'] + $invoice['total_price'];
            }
        }
        $d['all']['10-sun'] = $d['all']['10-sun'] + $d['m']['10-sun'];
        if ($d['m']['10-sun']) {
            $d['m']['10-sun'] = Pi::api('api', 'order')->viewPrice($d['m']['10-sun'], true);
        } else {
            $d['m']['10-sun'] = '';
        }

        /* Line 2 */
        $d['m']['20'] = pmktime(0, 0, 0, $month, 20, $year);
        $d['m']['20-view'] = _date(pmktime(0, 0, 0, $month, 20, $year), array('pattern' => 'yyyy/MM/dd'));
        $d['m']['20-sun'] = 0;
        $d['m']['20-invoice'] = array();
        foreach ($invoices as $invoice) {
            if ($invoice['time_duedate'] < pmktime(0, 0, 0, $month, 20, $year)) {
                $d['m']['20-invoice'][$invoice['id']] = $invoice;
                $d['m']['20-sun'] = $d['m']['20-sun'] + $invoice['total_price'];
            }
        }
        $d['all']['20-sun'] = $d['all']['20-sun'] + $d['m']['20-sun'];
        if ($d['m']['20-sun']) {
            $d['m']['20-sun'] = Pi::api('api', 'order')->viewPrice($d['m']['20-sun'], true);
        } else {
            $d['m']['20-sun'] = '';
        }
        
        /* Line 3 */
        $dayM = ($month == 12) ? 28 : 30;
        $d['m']['30'] = pmktime(0, 0, 0, $month, $dayM, $year);
        $d['m']['30-view'] = _date(pmktime(0, 0, 0, $month, $dayM, $year), array('pattern' => 'yyyy/MM/dd'));
        $d['m']['30-sun'] = 0;
        $d['m']['30-invoice'] = array();
        foreach ($invoices as $invoice) {
            if ($invoice['time_duedate'] < pmktime(0, 0, 0, $month, $dayM, $year)) {
                $d['m']['30-invoice'][$invoice['id']] = $invoice;
                $d['m']['30-sun'] = $d['m']['30-sun'] + $invoice['total_price'];
            }
        }
        $d['all']['30-sun'] = $d['all']['30-sun'] + $d['m']['30-sun'];
        if ($d['m']['30-sun']) {
            $d['m']['30-sun'] = Pi::api('api', 'order')->viewPrice($d['m']['30-sun'], true);
        } else {
            $d['m']['30-sun'] = '';
        }
        
        /* Make other lines */
        for ($i=0; $i < 13; $i++) {

            if ($i == 0) {
                $month = pdate('m', strtotime('now'));
                $year = pdate('Y', strtotime('now'));
            } else {
                $month = pdate('m', strtotime(sprintf('+%s month', $i)));
                $year = pdate('Y', strtotime(sprintf('+%s month', $i)));
            }
            
            /* Line 1 */
            $d[$i]['10'] = pmktime(0, 0, 0, $month, 10, $year);
            $d[$i]['10-view'] = _date(pmktime(0, 0, 0, $month, 10, $year), array('pattern' => 'yyyy/MM/dd'));
            $d[$i]['10-sun'] = 0;
            $d[$i]['10-invoice'] = array();
            foreach ($invoices as $invoice) {
                //if ($invoice['time_duedate'] == pmktime(0, 0, 0, $month, 10, $year)) {
                if ($invoice['time_duedate'] > pmktime(0, 0, 0, $month, 8, $year) && $invoice['time_duedate'] < pmktime(0, 0, 0, $month, 12, $year)) {    
                    $d[$i]['10-invoice'][$invoice['id']] = $invoice;
                    $d[$i]['10-sun'] = $d[$i]['10-sun'] + $invoice['total_price'];
                }
            }
            $d['all']['10-sun'] = $d['all']['10-sun'] + $d[$i]['10-sun'];
            if ($d[$i]['10-sun']) {
                $d[$i]['10-sun'] = Pi::api('api', 'order')->viewPrice($d[$i]['10-sun'], true);
            } else {
                $d[$i]['10-sun'] = '';
            }
            
            /* Line 2 */
            $d[$i]['20'] = pmktime(0, 0, 0, $month, 20, $year);
            $d[$i]['20-view'] = _date(pmktime(0, 0, 0, $month, 20, $year), array('pattern' => 'yyyy/MM/dd'));
            $d[$i]['20-sun'] = 0;
            $d[$i]['20-invoice'] = array();
            foreach ($invoices as $invoice) {
                //if ($invoice['time_duedate'] == pmktime(0, 0, 0, $month, 20, $year)) {
                if ($invoice['time_duedate'] > pmktime(0, 0, 0, $month, 18, $year) && $invoice['time_duedate'] < pmktime(0, 0, 0, $month, 22, $year)) {    
                    $d[$i]['20-invoice'][$invoice['id']] = $invoice;
                    $d[$i]['20-sun'] = $d[$i]['20-sun'] + $invoice['total_price'];
                }
            }
            $d['all']['20-sun'] = $d['all']['20-sun'] + $d[$i]['20-sun'];
            if ($d[$i]['20-sun']) {
                $d[$i]['20-sun'] = Pi::api('api', 'order')->viewPrice($d[$i]['20-sun'], true);
            } else {
                $d[$i]['20-sun'] = '';
            }

            /* Line 3 */
            $dayI = ($month == 12) ? 28 : 30;
            $d[$i]['30'] = pmktime(0, 0, 0, $month, $dayI, $year);
            $d[$i]['30-view'] = _date(pmktime(0, 0, 0, $month, $dayI, $year), array('pattern' => 'yyyy/MM/dd'));
            $d[$i]['30-sun'] = 0;
            $d[$i]['30-invoice'] = array();
            foreach ($invoices as $invoice) {
                $nextI = $i + 1;
                $monthNext = pdate('m', strtotime(sprintf('+%s month', $nextI)));
                $yearNext = pdate('Y', strtotime(sprintf('+%s month', $nextI)));
                //if ($invoice['time_duedate'] == pmktime(0, 0, 0, $month, 30, $year)) {
                if ($invoice['time_duedate'] > pmktime(0, 0, 0, $month, 28, $year) && $invoice['time_duedate'] < pmktime(0, 0, 0, $monthNext, 2, $yearNext)) {    
                    $d[$i]['30-invoice'][$invoice['id']] = $invoice;
                    $d[$i]['30-sun'] = $d[$i]['30-sun'] + $invoice['total_price'];
                }
            }
            $d['all']['30-sun'] = $d['all']['30-sun'] + $d[$i]['30-sun'];
            if ($d[$i]['30-sun']) {
                $d[$i]['30-sun'] = Pi::api('api', 'order')->viewPrice($d[$i]['30-sun'], true);
            } else {
                $d[$i]['30-sun'] = '';
            }
            
        }

        if ($d['all']['10-sun']) {
            $d['all']['10-sun-view'] = Pi::api('api', 'order')->viewPrice($d['all']['10-sun'], true);
        } else {
            $d['all']['10-sun-view'] = '';
        }

        if ($d['all']['20-sun']) {
            $d['all']['20-sun-view'] = Pi::api('api', 'order')->viewPrice($d['all']['20-sun'], true);
        } else {
            $d['all']['20-sun-view'] = '';
        }

        if ($d['all']['30-sun']) {
            $d['all']['30-sun-view'] = Pi::api('api', 'order')->viewPrice($d['all']['30-sun'], true);
        } else {
            $d['all']['30-sun-view'] = '';
        }
        
        $d['total'] = $d['all']['10-sun'] + $d['all']['20-sun'] + $d['all']['30-sun'];
        if ($d['total']) {
            $d['total-view'] = Pi::api('api', 'order')->viewPrice($d['total'], true);
        } else {
            $d['total-view'] = '';
        }

        return $d;
    }
}
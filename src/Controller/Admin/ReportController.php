<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */

namespace Module\Order\Controller\Admin;

use Module\Order\Form\CreditFilter;
use Module\Order\Form\CreditForm;
use Module\Order\Form\CreditSettingFilter;
use Module\Order\Form\CreditSettingForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;

class ReportController extends ActionController
{
    public function indexAction()
    {
        $select = Pi::model('order', $this->getModule())->select()->where(['type_commodity = "booking"', 'status_order = ?' => \Module\Order\Model\Order::STATUS_ORDER_VALIDATED])->order(array('time_create DESC', 'id DESC'));
        $rowset = Pi::model('order', $this->getModule())->selectWith($select);
        $orders = array();
        $idsBooking = [];
        $idsItem = [];
        foreach ($rowset as $row) {
            $extra = json_decode($row->extra, true);
            if (isset($extra['booking'])) {
                $order =  $row->toArray();
                $order['id_booking'] = $extra['booking'];
                $order['id_item'] = $extra['item'];
                $orders[] = $order;
                $idsBooking[] = $extra['booking'];
                $idsItem[] = $extra['item'];
            }
        }

        $bookings = Pi::api('booking', 'guide')->getBookingsById($idsBooking);
        $items = Pi::api('item', 'guide')->getListFromId($idsItem);

        $csv = [];
        foreach ($orders as $order) {
            $booking = $bookings[$order['id_booking']];
            $services = json_decode($booking['services'], true);
            $item = $items[$order['id_item']];

            $touristTax = 0;

            foreach ($services['other-fee'] as $service) {
                if ($service['service'] == 'touristtax') {
                    $touristTax =  $service['total_price'];
                }
            }

            $year = date('Y', $order['time_create']);

            $csv[] = [
                'year' => $year,
                'item' => $item['title'],
                'booking' => $booking['id'],
                'customer' => $booking['first_name'] . ' ' . $booking['last_name'],
                'from' => $booking['date_start'] ,
                'to' => $booking['date_end'],
                'nights' => round((strtotime($booking['date_end']) - strtotime($booking['date_start'])) / (24 * 3600)),
                'adults' => $services['containers'][0]['quantity_adult'],
                'children' => $services['containers'][0]['quantity_children'],
                'children ages' => join(',', $services['containers'][0]['children_ages']),
                'tourist tax' => $touristTax,
            ];
        }

        header("Content-disposition: attachment; filename=report.csv");
        header("Content-Type: text/csv");

        $f = fopen("php://output", 'w');

        fputs($f, chr(0xEF) . chr(0xBB) . chr(0xBF) );

        fputcsv($f,array_keys($csv[0]),';','"');

        foreach ($csv as $data) {
            fputcsv($f, $data,';','"');
        }
        fclose($f);
        exit;
    }
}
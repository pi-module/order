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

namespace Module\Order\Gateway;

use Pi;
use Zend\Json\Json;

abstract class AbstractGateway
{
    const TYPE_FORM = 0;
    const TYPE_REST = 1;

    protected $_type      = AbstractGateway::TYPE_FORM;
    protected $_needToken = false;

    public $gatewayAdapter = '';

    public $gatewayRow = '';

    public $gatewayIsActive = '';

    public $gatewayOption = [];

    public $gatewayPayInformation = [];

    public $gatewaySettingForm = [];

    public $gatewayPayForm = [];

    public $gatewayInformation = [];

    public $gatewayInvoice = [];

    public $gatewayOrder = [];

    public $gatewayRedirectUrl = '';

    public $gatewayBackUrl = '';

    public $gatewayNotifyUrl = '';

    public $gatewayError = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setInformation();
        $this->setAdapter();
        $this->setRow();
        $this->setOption();
        $this->setSettingForm();
        if ($this->_type == AbstractGateway::TYPE_FORM) {
            $this->setPayForm();
        }
        $this->setIsActive();
    }

    abstract public function setAdapter();

    abstract public function setInformation();

    abstract public function setSettingForm();

    abstract public function setRedirectUrl();

    abstract public function verifyPayment($value, $processing);

    static public function getAllList()
    {
        $list        = [];
        $gatewayPath = 'usr/module/order/src/Gateway';
        $fullPath    = Pi::path($gatewayPath);
        $allPath     = scandir($fullPath);
        foreach ($allPath as $path) {
            $dir = sprintf(Pi::path('usr/module/order/src/Gateway/%s'), $path);
            if (is_dir($dir)) {
                $class = sprintf('Module\Order\Gateway\%s\Gateway', $path);
                if (class_exists($class)) {
                    $gateway = new $class;
                    if (is_object($gateway)) {
                        $list[$path] = $gateway->canonize();
                    }
                }
            }
        }
        return $list;
    }

    static public function getActiveList()
    {
        $where = ['status' => 1];
        // Get list of story
        $select = Pi::model('gateway', 'order')->select()->where($where);
        $rowset = Pi::model('gateway', 'order')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $item[$row->id]           = $row->toArray();
            $item[$row->id]['option'] = Json::decode($item[$row->id]['option'], true);
            $dir                      = sprintf(Pi::path('usr/module/order/src/Gateway/%s'), $item[$row->id]['path']);
            if (is_dir($dir)) {
                $class = sprintf('Module\Order\Gateway\%s\Gateway', $item[$row->id]['path']);
                if (class_exists($class)) {
                    $obj = new $class;
                    if (is_object($obj)) {
                        $list[$item[$row->id]['id']] = $item[$row->id];
                    }
                }
            }
        }
        return $list;
    }

    static public function getActiveName()
    {
        $where = ['status' => 1];
        // Get list of story
        $select = Pi::model('gateway', 'order')->select()->where($where);
        $rowset = Pi::model('gateway', 'order')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->path] = $row->title;
        }

        $config = Pi::service('registry')->config->read('order');
        if ($config['payment_offline']) {
            $list['Offline'] = $config['payment_offline_title'];
        }

        return $list;
    }

    static public function getGateway($adapter = '')
    {
        if (!empty($adapter)) {
            $class = sprintf('Module\Order\Gateway\%s\Gateway', $adapter);
            if (class_exists($class)) {
                $gateway = new $class;
                if (is_object($gateway)) {
                    return $gateway;
                }
            }
        }
        return false;
    }

    static public function getGatewayInfo($adapter = '')
    {
        if (!empty($adapter)) {
            $gateway = Pi::model('gateway', 'order')->find($adapter, 'path')->toArray();
            return $gateway;
        }
        return false;
    }

    static public function getGatewayMessage($adapter, $log)
    {
        if (!empty($adapter)) {
            $class = sprintf('Module\Order\Gateway\%s\Gateway', $adapter);
            if (class_exists($class)) {
                $message = $class::setMessage($log);
                return $message;
            }
        }
        return false;
    }

    protected function canonize()
    {
        $canonize = [];
        if ($this->gatewayIsActive == -1) {
            $canonize = $this->gatewayInformation;
        } else {
            $canonize = array_merge($this->gatewayRow, $this->gatewayInformation);
        }
        $canonize['status']  = $this->gatewayIsActive;
        $canonize['option']  = $this->gatewayOption;
        $canonize['adapter'] = $this->gatewayAdapter;
        return $canonize;
    }


    protected function setRow()
    {
        $gateway = Pi::model('gateway', 'order')->find($this->gatewayAdapter, 'path');
        if (is_object($gateway)) {
            $this->gatewayRow = $gateway->toArray();
        }
        return $this;
    }

    protected function setOption()
    {
        if (is_array($this->gatewayRow) && isset($this->gatewayRow['option'])) {
            $this->gatewayOption = Json::decode($this->gatewayRow['option'], true);
        }
        return $this;
    }

    protected function setIsActive()
    {
        $this->gatewayIsActive = -1;
        if (is_array($this->gatewayRow) && isset($this->gatewayRow['status'])) {
            $this->gatewayIsActive = $this->gatewayRow['status'];
        }
        return $this;
    }

    protected function setBackUrl()
    {
        $this->gatewayBackUrl = Pi::url(
            Pi::service('url')->assemble(
                'order', [
                'module'     => 'order',
                'controller' => 'payment',
                'action'     => 'result',
            ]
            )
        );
    }

    protected function setCancelUrl()
    {
        $this->gatewayCancelUrl = Pi::url(
            Pi::service('url')->assemble(
                'order', [
                'module'     => 'order',
                'controller' => 'payment',
                'action'     => 'cancel',
            ]
            )
        );
    }

    protected function setFinishUrl()
    {
        $this->gatewayFinishUrl = Pi::url(
            Pi::service('url')->assemble(
                'order', [
                'module'     => 'order',
                'controller' => 'payment',
                'action'     => 'finish',
                'id'         => (new Pi\Filter\Slug())->filter($this->gatewayAdapter),
            ]
            )
        );
    }

    protected function setNotifyUrl()
    {
        $this->gatewayNotifyUrl = Pi::url(
            Pi::service('url')->assemble(
                'order', [
                'module'     => 'order',
                'controller' => 'payment',
                'action'     => 'notify',
            ]
            )
        );
    }

    public function setOrder($order = [])
    {
        if (is_array($order) && !empty($order)) {
            $this->gatewayOrder = $order;
            $this->setBackUrl();
            $this->setCancelUrl();
            $this->setFinishUrl();
            $this->setNotifyUrl();
            $this->setRedirectUrl();
        }
        return $this;
    }

    protected function setLog($value, $message)
    {
        $amount = 0;
        for ($i = 1; $i < $this->gatewayPayInformation['nb_product']; ++$i) {
            $amount += $this->gatewayPayInformation['amount_' . $i] - $this->gatewayPayInformation['discount_price_' . $i]
                - $this->gatewayPayInformation['unconsumed_' . $i];
        }

        $log            = [];
        $log['gateway'] = $this->gatewayAdapter;
        $log['value']   = $value;
        $log['amount']  = $amount;
        $log['message'] = $message;
        $log['order']   = $this->gatewayOrder['id'];
        Pi::api('log', 'order')->setLog($log);

    }

    public function getDescription()
    {
        return null;
    }


}
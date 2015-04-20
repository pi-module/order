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
namespace Module\Order\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion    = $e->getParam('version');

        // Set order model
        $orderModel       = Pi::model('order', $this->module);
        $orderTable       = $orderModel->getTable();
        $orderAdapter     = $orderModel->getAdapter();

        // Set invoice model
        $invoiceModel     = Pi::model('invoice', $this->module);
        $invoiceTable     = $invoiceModel->getTable();
        $invoiceAdapter   = $invoiceModel->getAdapter();

        // Set basket model
        $basketModel      = Pi::model('basket', $this->module);
        $basketTable      = $basketModel->getTable();
        $basketAdapter    = $basketModel->getAdapter();

        if (version_compare($moduleVersion, '1.3.6', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `id_number` varchar(255) NOT NULL default ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.4.1', '<')) {
            // Alter table field add credit_price
            $sql = sprintf("ALTER TABLE %s ADD `credit_price` decimal(16,8) NOT NULL default '0.00'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.4.3', '<')) {
            // Alter table field change type_payment
            $sql = sprintf("ALTER TABLE %s CHANGE `type` `type_payment` enum('free','onetime','recurring','installment') NOT NULL default 'onetime'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `type_commodity` enum('product','service') NOT NULL default 'product'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.4.8', '<')) {
            // Alter table field add credit_price
            $sql = sprintf("ALTER TABLE %s ADD `extra` text", $basketTable);
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.5.3', '<')) {
            // Alter table field add credit_price
            $sql = sprintf("ALTER TABLE %s ADD `extra` text", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                                   . $exception->getMessage(),
                ));
                return false;
            }
        }
        
        return true;
    }
}   
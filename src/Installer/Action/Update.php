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
        $moduleVersion = $e->getParam('version');

        // Set order model
        $orderModel = Pi::model('order', $this->module);
        $orderTable = $orderModel->getTable();
        $orderAdapter = $orderModel->getAdapter();

        // Set invoice model
        $invoiceModel = Pi::model('invoice', $this->module);
        $invoiceTable = $invoiceModel->getTable();
        $invoiceAdapter = $invoiceModel->getAdapter();

        // Set basket model
        $basketModel = Pi::model('basket', $this->module);
        $basketTable = $basketModel->getTable();
        $basketAdapter = $basketModel->getAdapter();

        // Set customer model
        $customerModel = Pi::model('customer', $this->module);
        $customerTable = $customerModel->getTable();
        $customerAdapter = $customerModel->getAdapter();

        // Set credit model
        $creditModel = Pi::model('credit', $this->module);
        $creditTable = $creditModel->getTable();
        $creditAdapter = $creditModel->getAdapter();

        // Set history model
        $historyModel = Pi::model('history', $this->module);
        $historyTable = $historyModel->getTable();
        $historyAdapter = $historyModel->getAdapter();

        if (version_compare($moduleVersion, '1.3.6', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `id_number` varchar(255) NOT NULL default ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
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
                    'status' => false,
                    'message' => 'Table alter query failed: '
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
                    'status' => false,
                    'message' => 'Table alter query failed: '
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
                    'status' => false,
                    'message' => 'Table alter query failed: '
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
                    'status' => false,
                    'message' => 'Table alter query failed: '
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
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.5.9', '<')) {
            // Alter table field add code
            $sql = sprintf("ALTER TABLE %s ADD `code` varchar(16) NOT NULL default ''", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            // Add code for all old invoices
            $select = $invoiceModel->select();
            $rowset = $invoiceModel->selectWith($select);
            foreach ($rowset as $row) {
                $row->code = Pi::api('invoice', 'order')->generatCode($row->id);
                $row->save();
            }
        }

        if (version_compare($moduleVersion, '1.6.2', '<')) {
            // Add table : customer
            $sql = <<<'EOD'
CREATE TABLE `{customer}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `uid` int(10) unsigned NOT NULL default '0',
    `ip` char(15) NOT NULL default '',
    `id_number` varchar(255) NOT NULL default '',
    `first_name` varchar(255) NOT NULL default '',
    `last_name` varchar(255) NOT NULL default '',
    `email` varchar(64) NOT NULL default '',
    `phone` varchar(16) NOT NULL default '',
    `mobile` varchar(16) NOT NULL default '',
    `address1` text,
    `address2` text,
    `country` varchar(64) NOT NULL default '',
    `state` varchar(64) NOT NULL default '',
    `city` varchar(64) NOT NULL default '',
    `zip_code` varchar(16) NOT NULL default '',
    `company` varchar(255) NOT NULL default '',
    `company_id` varchar(255) NOT NULL default '',
    `company_vat` varchar(255) NOT NULL default '',
    `user_note` text,
    `time_create` int(10) unsigned NOT NULL default '0',
    `time_update` int(10) unsigned NOT NULL default '0',
    `status` tinyint(1) unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`),
    KEY `uid` (`uid`),
    KEY `status` (`status`),
    KEY `time_create` (`time_create`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'SQL schema query for author table failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }
        }

        if (version_compare($moduleVersion, '1.6.4', '<')) {
            // Alter table field add code
            $sql = sprintf("ALTER TABLE %s ADD `address_type` enum('delivery','invoicing') NOT NULL default 'delivery'", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.0', '<')) {
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `setup_price` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `setup_price` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $basketTable);
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `setup_price` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.1', '<')) {
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `delivery` INT(10) UNSIGNED NOT NULL DEFAULT '0'", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `location` INT(10) UNSIGNED NOT NULL DEFAULT '0'", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.2', '<')) {
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `can_pay` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `can_pay` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.7', '<')) {
            // Alter table field CHANGE
            $sql = sprintf("ALTER TABLE %s CHANGE `promo_type` `promotion_type` VARCHAR(64)  NOT NULL DEFAULT ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
            // Alter table field CHANGE
            $sql = sprintf("ALTER TABLE %s CHANGE `promo_value` `promotion_value` VARCHAR(64) NOT NULL DEFAULT ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.8', '<')) {
            // Add table : credit
            $sql = <<<'EOD'
CREATE TABLE `{credit}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time_update` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `amount`      DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'SQL schema query for author table failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }

            // Add table : history
            $sql = <<<'EOD'
CREATE TABLE `{history}` (
  `id`                 INT(10) UNSIGNED              NOT NULL AUTO_INCREMENT,
  `uid`                INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `time_create`        INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `order`              INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `invoice`            INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `amount`             DECIMAL(16, 2)                NOT NULL DEFAULT '0.00',
  `amount_old`         DECIMAL(16, 2)                NOT NULL DEFAULT '0.00',
  `status`             TINYINT(1) UNSIGNED           NOT NULL DEFAULT '0',
  `status_fluctuation` ENUM ('increase', 'decrease') NOT NULL DEFAULT 'increase',
  `status_action`      ENUM ('automatic', 'manual')  NOT NULL DEFAULT 'automatic',
  `message_user`       TEXT,
  `message_admin`      TEXT,
  `ip`                 CHAR(15)                      NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time_create` (`time_create`),
  KEY `order` (`order`),
  KEY `invoice` (`invoice`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'SQL schema query for author table failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }
        }

        if (version_compare($moduleVersion, '1.8.5', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `amount_detail` VARCHAR(255) NOT NULL DEFAULT ''", $creditTable);
            try {
                $creditAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.8.6', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `module` VARCHAR(64) NOT NULL DEFAULT ''", $historyTable);
            try {
                $historyAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.8.8', '<')) {
            // Add table : access
            $sql = <<<'EOD'
CREATE TABLE `{access}` (
  `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uid`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `item_key`    VARCHAR(128)        NOT NULL DEFAULT '',
  `order`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_start`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_end`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `ip`          CHAR(15)            NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_key` (`item_key`),
  KEY `uid` (`uid`),
  KEY `order` (`order`),
  KEY `status` (`status`),
  KEY `time_start` (`time_start`),
  KEY `time_end` (`time_end`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'SQL schema query for author table failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }
        }

        if (version_compare($moduleVersion, '1.9.1', '<')) {
            // Alter table field add amount_new
            $sql = sprintf("ALTER TABLE %s ADD `amount_new` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $historyTable);
            try {
                $historyAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }


         if (version_compare($moduleVersion, '1.10.0', '<')) {
            $sql = <<<'EOD'
CREATE TABLE `{promocode}` (
  `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(16) NOT NULL,
  `promo`         TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `time_start`      INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `time_end`        INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `module`          VARCHAR(16) NOT NULL,
  PRIMARY KEY (`id`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'SQL schema query for author table failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }
        }


        return true;
    }
}
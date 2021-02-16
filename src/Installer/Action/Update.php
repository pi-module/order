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

namespace Module\Order\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Laminas\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', [$this, 'updateSchema']);
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
        $orderModel   = Pi::model('order', $this->module);
        $orderTable   = $orderModel->getTable();
        $orderAdapter = $orderModel->getAdapter();

        // Set invoice model
        $invoiceModel   = Pi::model('invoice', $this->module);
        $invoiceTable   = $invoiceModel->getTable();
        $invoiceAdapter = $invoiceModel->getAdapter();

        // Set basket model
        $basketModel   = Pi::model('basket', $this->module);
        $basketTable   = $basketModel->getTable();
        $basketAdapter = $basketModel->getAdapter();

        // Set customer model
        $customerModel   = Pi::model('customer', $this->module);
        $customerTable   = $customerModel->getTable();
        $customerAdapter = $customerModel->getAdapter();

        // Set credit model
        $creditModel   = Pi::model('credit', $this->module);
        $creditTable   = $creditModel->getTable();
        $creditAdapter = $creditModel->getAdapter();

        // Set history model
        $historyModel   = Pi::model('history', $this->module);
        $historyTable   = $historyModel->getTable();
        $historyAdapter = $historyModel->getAdapter();

        $processModel   = Pi::model('processing', $this->module);
        $processTable   = $processModel->getTable();
        $processAdapter = $processModel->getAdapter();

        $logModel   = Pi::model('log', $this->module);
        $logTable   = $logModel->getTable();
        $logAdapter = $logModel->getAdapter();

        $orderAddressModel   = Pi::model('order_address', $this->module);
        $orderAddressTable   = $orderAddressModel->getTable();
        $orderAddressAdapter = $orderAddressModel->getAdapter();

        $customerAddressModel   = Pi::model('customer_address', $this->module);
        $customerAddressTable   = $customerAddressModel->getTable();
        $customerAddressAdapter = $customerAddressModel->getAdapter();

        $detailModel   = Pi::model('detail', $this->module);
        $detailTable   = $detailModel->getTable();
        $detailAdapter = $detailModel->getAdapter();

        $accessModel   = Pi::model('access', $this->module);
        $accessTable   = $accessModel->getTable();
        $accessAdapter = $accessModel->getAdapter();

        $creditHistoryModel   = Pi::model('credit_history', $this->module);
        $creditHistoryTable   = $creditHistoryModel->getTable();
        $creditHistoryAdapter = $creditHistoryModel->getAdapter();

        $orderInstallmentModel   = Pi::model('invoice_installment', $this->module);
        $orderInstallmentTable   = $orderInstallmentModel->getTable();
        $orderInstallmentAdapter = $orderInstallmentModel->getAdapter();

        $promocodeModel   = Pi::model('promocode', $this->module);
        $promocodeTable   = $promocodeModel->getTable();
        $promocodeAdapter = $promocodeModel->getAdapter();


        if (version_compare($moduleVersion, '1.3.6', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `id_number` varchar(255) NOT NULL default ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.4.1', '<')) {
            // Alter table field add credit_price
            $sql = sprintf("ALTER TABLE %s ADD `credit_price` decimal(16,8) NOT NULL default '0.00'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.4.3', '<')) {
            // Alter table field change type_payment
            $sql = sprintf(
                "ALTER TABLE %s CHANGE `type` `type_payment` enum('free','onetime','recurring','installment') NOT NULL default 'onetime'",
                $orderTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `type_commodity` enum('product','service') NOT NULL default 'product'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.4.8', '<')) {
            // Alter table field add credit_price
            $sql = sprintf("ALTER TABLE %s ADD `extra` text", $basketTable);
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.5.3', '<')) {
            // Alter table field add credit_price
            $sql = sprintf("ALTER TABLE %s ADD `extra` text", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.5.9', '<')) {
            // Alter table field add code
            $sql = sprintf("ALTER TABLE %s ADD `code` varchar(16) NOT NULL default ''", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            // Add code for all old invoices
            $select = $invoiceModel->select();
            $rowset = $invoiceModel->selectWith($select);
            foreach ($rowset as $row) {
                $row->code = Pi::api('invoice', 'order')->generateCode($row->id);
                $row->save();
            }
        }

        if (version_compare($moduleVersion, '1.6.2', '<')) {
            // Add table : customer
            $sql
                = <<<'EOD'
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
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for author table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }
        }

        if (version_compare($moduleVersion, '1.6.4', '<')) {
            // Alter table field add code
            $sql = sprintf("ALTER TABLE %s ADD `address_type` enum('delivery','invoicing') NOT NULL default 'delivery'", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.0', '<')) {
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `setup_price` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `setup_price` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $basketTable);
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `setup_price` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.1', '<')) {
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `delivery` INT(10) UNSIGNED NOT NULL DEFAULT '0'", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `location` INT(10) UNSIGNED NOT NULL DEFAULT '0'", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.2', '<')) {
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `can_pay` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            // Alter table field add
            $sql = sprintf("ALTER TABLE %s ADD `can_pay` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.7', '<')) {
            // Alter table field CHANGE
            $sql = sprintf("ALTER TABLE %s CHANGE `promo_type` `promotion_type` VARCHAR(64)  NOT NULL DEFAULT ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            // Alter table field CHANGE
            $sql = sprintf("ALTER TABLE %s CHANGE `promo_value` `promotion_value` VARCHAR(64) NOT NULL DEFAULT ''", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.7.8', '<')) {
            // Add table : credit
            $sql
                = <<<'EOD'
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
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for author table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }

            // Add table : history
            $sql
                = <<<'EOD'
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
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for author table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }
        }

        if (version_compare($moduleVersion, '1.8.5', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `amount_detail` VARCHAR(255) NOT NULL DEFAULT ''", $creditTable);
            try {
                $creditAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.8.6', '<')) {
            // Alter table field add id_number
            $sql = sprintf("ALTER TABLE %s ADD `module` VARCHAR(64) NOT NULL DEFAULT ''", $historyTable);
            try {
                $historyAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.9.1', '<')) {
            // Alter table field add amount_new
            $sql = sprintf("ALTER TABLE %s ADD `amount_new` DECIMAL(16, 2) NOT NULL DEFAULT '0.00'", $historyTable);
            try {
                $historyAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }


        if (version_compare($moduleVersion, '1.10.0', '<')) {
            $sql
                = <<<'EOD'
CREATE TABLE `{promocode}` (
  `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(16) NOT NULL,
  `promo`         TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `time_start`      INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `time_end`        INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `module`          VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for author table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }
        }

        if (version_compare($moduleVersion, '1.10.1', '<')) {
            // Alter table field add amount_new
            $sql = sprintf("UPDATE %s SET `city` = UPPER(city), last_name = UPPER(last_name)", $customerTable);
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.11.0', '<')) {
            // Alter table field add amount_new
            $sql = sprintf("ALTER TABLE %s CHANGE `invoice` `order` INT(10) NOT NULL ", $processTable);
            try {
                $processAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD `extra` TEXT NULL", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `invoice` `order` INT(10) NOT NULL ", $logTable);
            try {
                $logAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.0.2', '<')) {
            try {
                $select = $orderModel->select();
                $rowset = $orderModel->selectWith($select);
                $config = Pi::service('registry')->config->read('order');

                foreach ($rowset as $row) {
                    // Set value
                    $year  = date('Y', $row->time_create);
                    $count = Pi::model('order', 'order')->count(
                        [
                            'time_create >= ' . strtotime('01-01-' . $year),
                            'id < ' . $row->id,
                        ]
                    );
                    $num   = $year . sprintf('%03d', ($count + 1));
                    $code  = sprintf('%s-%s', $config['order_code_prefix'], $num);

                    $row->code = $code;
                    $row->save();
                }

                $select = $invoiceModel->select();
                $rowset = $invoiceModel->selectWith($select);
                $config = Pi::service('registry')->config->read('order');

                foreach ($rowset as $row) {
                    // Set value
                    $year  = date('Y', $row->time_create);
                    $count = Pi::model('invoice', 'order')->count(
                        [
                            'time_create >= ' . strtotime('01-01-' . $year),
                            'id < ' . $row->id,
                        ]
                    );
                    $num   = $year . sprintf('%03d', ($count + 1));
                    $code  = sprintf('%s-%s', $config['invoice_code_prefix'], $num);

                    $row->code = $code;
                    $row->save();
                }
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table update query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.0.4', '<')) {
            $sql = sprintf("RENAME TABLE %s TO %s;", $customerTable, $customerAddressTable);

            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for rename customer table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }

            $sql
                = <<<'EOD'
CREATE TABLE `{order_address}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_number`  VARCHAR(255) NOT NULL DEFAULT '',
  `type` ENUM('DELIVERY', 'INVOICING'),
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(64) NOT NULL DEFAULT '',
  `phone` varchar(16) NOT NULL DEFAULT '',
  `mobile` varchar(16) NOT NULL DEFAULT '',
  `address1` text,
  `address2` text,
  `country` varchar(64) NOT NULL DEFAULT '',
  `state` varchar(64) NOT NULL DEFAULT '',
  `city` varchar(64) NOT NULL DEFAULT '',
  `zip_code` varchar(16) NOT NULL DEFAULT '',
  `company` varchar(255) NOT NULL DEFAULT '',
  `company_id` varchar(255) NOT NULL DEFAULT '',
  `company_vat` varchar(255) NOT NULL DEFAULT '',
  `delivery` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `location` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for order_address table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }

            $columns = [
                'id',
                'id_number',
                'first_name',
                'last_name',
                'email',
                'phone',
                'mobile',
                'address1',
                'address2',
                'country',
                'state',
                'city',
                'zip_code',
                'company',
                'company_id',
                'company_vat',
                'delivery',
                'location',
            ];

            try {
                $select = $orderModel->select()->columns($columns);
                $rowset = $orderModel->selectWith($select);
                foreach ($rowset as $row) {
                    $values          = $row->toArray();
                    $values['order'] = $values['id'];
                    unset($values['id']);

                    $orderAddress   = $orderAddressModel->createRow();
                    $values['type'] = 'INVOICING';
                    $orderAddress->assign($values);
                    $orderAddress->save(false);

                    $orderAddress   = $orderAddressModel->createRow();
                    $values['type'] = 'DELIVERY';
                    $orderAddress->assign($values);
                    $orderAddress->save(false);
                }
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'data transfer failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf(
                "ALTER TABLE %s  DROP `id_number`, DROP `first_name`,   DROP `last_name`,  DROP `email`,  DROP `phone`,  DROP `mobile`,  DROP `address1`,  DROP `address2`,  DROP `country`,  DROP `state`,  DROP `city`,  DROP `zip_code`,  DROP `company`,  DROP `company_id`,  DROP `company_vat`,  DROP `delivery`,  DROP `location`;",
                $orderTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf(
                "ALTER TABLE %s ADD `delivery_favourite` TINYINT(1) UNSIGNED, ADD `invoicing_favourite` TINYINT(1) UNSIGNED, DROP `address_type`",
                $customerAddressTable
            );
            try {
                $customerAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for customer failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.1.0', '<')) {
            $sql = sprintf(
                "ALTER TABLE %s ADD `module` VARCHAR(64) NOT NULL DEFAULT '', ADD `product_type` VARCHAR(64) NOT NULL DEFAULT '', ADD `time_start` INT(10) UNSIGNED NOT NULL DEFAULT '0', ADD `time_end` INT(10) UNSIGNED NOT NULL DEFAULT '0', ADD `time_create` INT(10) UNSIGNED NOT NULL DEFAULT '0'",
                $basketTable
            );
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for basket failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("UPDATE %s SET time_create = UNIX_TIMESTAMP()", $basketTable);
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table update query for basket failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }


            $sql = sprintf(
                "UPDATE %s basket INNER JOIN %s `order` on basket.`order` = `order`.id SET basket.module = `order`.module_name, basket.product_type = `order`.module_table",
                $basketTable,
                $orderTable
            );
            try {
                $basketAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for basket failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf(
                "ALTER TABLE %s  DROP `module_name`, DROP `module_table`, DROP `module_item`, DROP `time_start`, DROP `time_end`, DROP `time_finish`",
                $orderTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s  DROP `uid`, DROP `ip`", $invoiceTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("RENAME TABLE %s TO %s;", $basketTable, $detailTable);
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for rename basket table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }

            $sql = sprintf(
                "ALTER TABLE %s ADD `create_by` ENUM ('ADMIN', 'USER') NOT NULL DEFAULT 'USER', ADD `type` ENUM ('NORMAL', 'CREDIT') NOT NULL DEFAULT 'NORMAL'",
                $invoiceTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for invoice failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            $sql = sprintf("ALTER TABLE %s ADD `create_by` ENUM ('ADMIN', 'USER') NOT NULL DEFAULT 'USER'", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            // access table reqiresd : voltan
            /* $sql = sprintf("DROP TABLE %s;", $accessTable);
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for drop access table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            } */

            $sql = sprintf("RENAME TABLE %s TO %s;", $historyTable, $creditHistoryTable);
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for rename history table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }

            $sql
                = <<<'EOD'
CREATE TABLE `{installment}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `number` smallint(3) UNSIGNED NOT NULL DEFAULT '1',
  `commission`  DECIMAL(16, 2)  NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `{installment_product}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `installment` int(10) UNSIGNED NOT NULL,
  `module`         VARCHAR(64)  NOT NULL DEFAULT '',
  `product_type`   VARCHAR(64)  NOT NULL DEFAULT '',
  `product`  int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `product_type` (`product_type`),
  KEY `product` (`product`)
);

CREATE TABLE `{invoice_installment}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice` int(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `count`  smallint(3) UNSIGNED NOT NULL DEFAULT '1',
  `gateway`         VARCHAR(64)                                          NOT NULL DEFAULT 'offline',
  `status_payment`  TINYINT(1) UNSIGNED                                  NOT NULL DEFAULT '0',
  `time_payment`    INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `time_duedate`   INT(10) UNSIGNED                                      NOT NULL DEFAULT '0',
  `due_price`      DECIMAL(16, 2)                                        NOT NULL DEFAULT '0.00',
  `credit_price`   DECIMAL(16, 8)                                        NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `invoice` (`invoice`)
);          
EOD;

            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for installment tables failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }

            try {
                $select      = $orderModel->select()->columns(['id', 'status_payment', 'time_payment', 'paid_price', 'status_order']);
                $rowsetOrder = $orderModel->selectWith($select);
                foreach ($rowsetOrder as $rowOrder) {
                    if (in_array($rowOrder->status_order, [1, 3, 4, 6, 7])) {
                        $rowOrder->status_order = \Module\Order\Model\Order::STATUS_ORDER_VALIDATED;
                        $rowOrder->save();
                    }
                    if (in_array($rowOrder->status_order, [5])) {
                        $rowOrder->status_order = \Module\Order\Model\Order::STATUS_ORDER_CANCELLED;
                        $rowOrder->save();
                    }
                    $select        = $invoiceModel->select()->columns(['id', 'time_duedate', 'credit_price', 'gateway'])->where(['order' => $rowOrder->id]);
                    $rowsetInvoice = $invoiceModel->selectWith($select);

                    if ($rowsetInvoice->count()) {
                        foreach ($rowsetInvoice as $rowInvoice) {
                            $values           = [
                                'invoice'        => $rowInvoice->id,
                                'count'          => 1,
                                'gateway'        => $rowInvoice->gateway,
                                'status_payment' => $rowOrder->status_payment == 2 ? \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID
                                    : \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID,
                                'time_payment'   => $rowOrder->time_payment,
                                'time_duedate'   => $rowInvoice->time_duedate,
                                'due_price'      => $rowOrder->paid_price,
                                'credit_price'   => $rowInvoice->credit_price,
                            ];
                            $orderInstallment = $orderInstallmentModel->createRow();
                            $orderInstallment->assign($values);
                            $orderInstallment->save(false);
                        }
                    } else {
                        $values           = [
                            'invoice'        => 0,
                            'count'          => 1,
                            'gateway'        => '',
                            'status_payment' => $rowOrder->status_payment == 2 ? \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_PAID
                                : \Module\Order\Model\Invoice\Installment::STATUS_PAYMENT_UNPAID,
                            'time_duedate'   => $rowInvoice->time_duedate,
                            'time_payment'   => $rowOrder->time_payment,
                            'due_price'      => $rowOrder->paid_price,
                            'credit_price'   => 0,
                        ];
                        $orderInstallment = $orderInstallmentModel->createRow();
                        $orderInstallment->assign($values);
                        $orderInstallment->save(false);
                    }
                }
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'data transfer failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            $sql = sprintf(
                "ALTER TABLE %s DROP `status_payment`, DROP `time_payment`, DROP `product_price`, DROP `discount_price`, DROP `shipping_price`, DROP `packing_price`, DROP `setup_price`, DROP `vat_price`, DROP `total_price`, DROP `paid_price`, DROP `gateway`",
                $orderTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            $sql = sprintf(
                "ALTER TABLE %s  DROP `time_payment`, DROP `product_price`, DROP `discount_price`, DROP `shipping_price`, DROP `packing_price`, DROP `setup_price`, DROP `vat_price`, DROP `total_price`, DROP `paid_price`, DROP `gateway`, DROP `time_duedate`, DROP `can_pay`, DROP `extra`, DROP `credit_price`",
                $invoiceTable
            );
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for invoice failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            $sql = sprintf("ALTER TABLE %s DROP `total_price`", $detailTable);
            try {
                $detailAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for detail failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.0', '<')) {
            try {
                $select = $detailModel->select();
                $rowset = $detailModel->selectWith($select);
                foreach ($rowset as $row) {
                    if ($row->extra != null) {
                        $extra = json_decode($row->extra, true);
                        $arr   = $extra['product'];
                        unset($extra['product']);
                        $extra           = array_merge($extra, $arr);
                        $row->time_start = $extra['time_start'];
                        $row->time_end   = $extra['time_end'];
                        unset($extra['time_start']);
                        unset($extra['time_end']);
                        $row->extra = json_encode($extra);
                        $row->save();
                    }
                }
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'data transfer failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD `type_payment` ENUM ('free', 'onetime', 'recurring', 'installment') NOT NULL DEFAULT 'onetime'", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf(
                "UPDATE %s invoice INNER JOIN %s `order` on invoice.`order` = `order`.id SET invoice.type_payment = `order`.type_payment",
                $invoiceTable,
                $orderTable
            );
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for basket failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            $sql = sprintf("ALTER TABLE %s DROP `type_payment`", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf(
                "ALTER TABLE %s  CHANGE `time_end` `time_end` INT(10) UNSIGNED NULL DEFAULT '0',  CHANGE `time_start` `time_start` INT(10) UNSIGNED NULL DEFAULT '0'",
                $detailTable
            );
            try {
                $detailAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }
        if (version_compare($moduleVersion, '2.2.1', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `default_gateway` varchar(64) NOT NULL", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD `comment` TEXT", $orderInstallmentTable);
            try {
                $orderInstallmentAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.2', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `showcode` tinyint(1) NOT NULL", $promocodeTable);
            try {
                $promocodeAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.3', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `time_order` int(10) UNSIGNED NOT NULL", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
            $sql = sprintf("UPDATE %s SET `time_order` = `time_create`", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD `time_invoice` int(10) UNSIGNED NOT NULL", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("UPDATE %s SET `time_invoice` = `time_create`", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD `admin_note` VARCHAR(511) NULL DEFAULT ''", $detailTable);
            try {
                $detailAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.4', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `extra` TEXT", $invoiceTable);
            try {
                $invoiceAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }


        if (version_compare($moduleVersion, '2.2.5', '<')) {
            // Alter table field change type_payment
            $sql = sprintf(
                "ALTER TABLE %s CHANGE `type_commodity` `type_commodity` enum('product','service', 'booking') NOT NULL default 'product'",
                $orderTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.6', '<')) {
            $sql = sprintf(
                "
              ALTER TABLE %s 
              ADD `birthday`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
              ADD `account_type`        ENUM ('none', 'individual', 'company') NULL DEFAULT 'none',
              ADD `company_address1`    VARCHAR(255)        NOT NULL DEFAULT '',
              ADD `company_address2`    VARCHAR(255)        NOT NULL DEFAULT '',
              ADD `company_country`     VARCHAR(64)         NOT NULL DEFAULT '',
              ADD `company_state`       VARCHAR(64)         NOT NULL DEFAULT '',
              ADD `company_city`        VARCHAR(64)         NOT NULL DEFAULT '',
              ADD `company_zip_code`    VARCHAR(16)         NOT NULL DEFAULT ''",
                $orderAddressTable
            );
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }

            $sql = sprintf(
                "
              ALTER TABLE %s 
              ADD `birthday`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
              ADD `account_type`        ENUM ('none', 'individual', 'company') NULL DEFAULT 'none',
              ADD `company_address1`    VARCHAR(255)        NOT NULL DEFAULT '',
              ADD `company_address2`    VARCHAR(255)        NOT NULL DEFAULT '',
              ADD `company_country`     VARCHAR(64)         NOT NULL DEFAULT '',
              ADD `company_state`       VARCHAR(64)         NOT NULL DEFAULT '',
              ADD `company_city`        VARCHAR(64)         NOT NULL DEFAULT '',
              ADD `company_zip_code`    VARCHAR(16)         NOT NULL DEFAULT ''",
                $customerAddressTable
            );
            try {
                $customerAddressAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }
        if (version_compare($moduleVersion, '2.2.7', '<')) {
            // Alter table field change type_payment
            $sql = sprintf("ALTER TABLE %s CHANGE `product` `product` VARCHAR(10) NOT NULL DEFAULT '0'", $detailTable);
            try {
                $detailAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.10', '<')) {
            // Alter table field change type_payment
            $sql = sprintf("ALTER TABLE %s ADD `cancel_reason` TEXT", $orderTable);
            try {
                $orderAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.2.11', '<')) {
            // Alter table field change type_payment
            $sql = sprintf("ALTER TABLE %s CHANGE `product` `product` VARCHAR(64) NOT NULL DEFAULT '0'", $detailTable);
            try {
                $detailAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.3.0', '<')) {
            // Alter table field change type_payment
            $sql = sprintf("ALTER TABLE %s ADD `extra` TEXT", $orderInstallmentTable);


            try {
                $orderInstallmentAdapter->query($sql, 'execute');
                $sql = sprintf(
                    "UPDATE `%s` inst
JOIN `%s` invoice ON invoice.id = inst.invoice
JOIN `%s` ord ON ord.id = invoice.order
SET inst.extra = ord.extra",
                    $orderInstallmentTable,
                    $invoiceTable,
                    $orderTable
                );

                $orderInstallmentAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        if (version_compare($moduleVersion, '2.3.5', '<')) {
            $sql
                = <<<'EOD'
CREATE TABLE `{subscription_detail}`
(
    `id`                       int(10)          NOT NULL AUTO_INCREMENT,
    `uid`                      int(10)          NOT NULL DEFAULT '0',
    `order`                    INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `subscription_id`          VARCHAR(255)     NOT NULL DEFAULT '',
    `subscription_product`     VARCHAR(255)     NOT NULL DEFAULT '',
    `subscription_interval`    VARCHAR(255)     NOT NULL DEFAULT '',
    `subscription_status`      VARCHAR(255)     NOT NULL DEFAULT '',
    `subscription_customer`    VARCHAR(255)     NOT NULL DEFAULT '',
    `subscription_create_time` VARCHAR(255)     NOT NULL DEFAULT '',
    `current_period_start`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `current_period_end`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `time_create`              INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `extra`                    TEXT,
    PRIMARY KEY (`id`),
    KEY `subscription_id` (`subscription_id`),
    KEY `subscription_product` (`subscription_product`),
    KEY `uid` (`uid`)
);

CREATE TABLE `{subscription_customer}`
(
    `id`       int(10)      NOT NULL AUTO_INCREMENT,
    `uid`      int(10)      NOT NULL DEFAULT 0,
    `customer` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uid` (`uid`),
    UNIQUE KEY `customer` (`customer`)
);

CREATE TABLE `{subscription_product}`
(
    `id`                int(11)          NOT NULL AUTO_INCREMENT,
    `stripe_product_id` VARCHAR(64)      NOT NULL DEFAULT '',
    `stripe_price_id`   VARCHAR(64)      NOT NULL DEFAULT '',
    `service_id`        INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `service_title`     VARCHAR(64)      NOT NULL DEFAULT '',
    `service_module`    VARCHAR(64)      NOT NULL DEFAULT '',
    `service_amount`    DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
    `service_interval`  VARCHAR(64)      NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `service_id` (`service_id`),
    UNIQUE KEY `stripe_product_id` (`stripe_product_id`)
);
EOD;
            SqlSchema::setType($this->module);
            $sqlHandler = new SqlSchema;
            try {
                $sqlHandler->queryContent($sql);
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for author table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }
        }

        if (version_compare($moduleVersion, '2.3.8', '<')) {
            // Add table : access
            $sql
                = <<<'EOD'
CREATE TABLE `{access}`
(
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
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'SQL schema query for author table failed: '
                            . $exception->getMessage(),
                    ]
                );

                return false;
            }
        }

        if (version_compare($moduleVersion, '2.3.9', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `promotion_code` VARCHAR(64) NOT NULL DEFAULT ''", $detailTable);
            try {
                $detailAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult(
                    'db',
                    [
                        'status'  => false,
                        'message' => 'Table alter query for order failed: '
                            . $exception->getMessage(),
                    ]
                );
                return false;
            }
        }

        return true;
    }
}

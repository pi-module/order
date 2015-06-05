CREATE TABLE `{order}` (
# General
  `id`              INT(10) UNSIGNED                                    NOT NULL AUTO_INCREMENT,
  `uid`             INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `code`            VARCHAR(16)                                         NOT NULL DEFAULT '',
  `type_payment`    ENUM('free', 'onetime', 'recurring', 'installment') NOT NULL DEFAULT 'onetime',
  `type_commodity`  ENUM('product', 'service')                          NOT NULL DEFAULT 'product',
  `plan`            INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
# Module
  `module_name`     VARCHAR(64)                                         NOT NULL DEFAULT '',
  `module_table`    VARCHAR(64)                                         NOT NULL DEFAULT '',
  `module_item`     INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
# Customer information
  `ip`              CHAR(15)                                            NOT NULL DEFAULT '',
  `id_number`       VARCHAR(255)                                        NOT NULL DEFAULT '',
  `first_name`      VARCHAR(255)                                        NOT NULL DEFAULT '',
  `last_name`       VARCHAR(255)                                        NOT NULL DEFAULT '',
  `email`           VARCHAR(64)                                         NOT NULL DEFAULT '',
  `phone`           VARCHAR(16)                                         NOT NULL DEFAULT '',
  `mobile`          VARCHAR(16)                                         NOT NULL DEFAULT '',
  `address1`        TEXT,
  `address2`        TEXT,
  `country`         VARCHAR(64)                                         NOT NULL DEFAULT '',
  `state`           VARCHAR(64)                                         NOT NULL DEFAULT '',
  `city`            VARCHAR(64)                                         NOT NULL DEFAULT '',
  `zip_code`        VARCHAR(16)                                         NOT NULL DEFAULT '',
  `company`         VARCHAR(255)                                        NOT NULL DEFAULT '',
  `company_id`      VARCHAR(255)                                        NOT NULL DEFAULT '',
  `company_vat`     VARCHAR(255)                                        NOT NULL DEFAULT '',
# Notes
  `user_note`       TEXT,
  `admin_note`      TEXT,
# Needed times
  `time_create`     INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `time_payment`    INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `time_delivery`   INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `time_finish`     INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `time_start`      INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `time_end`        INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
# Needed status
  `status_order`    TINYINT(1) UNSIGNED                                 NOT NULL DEFAULT '0',
  `status_payment`  TINYINT(1) UNSIGNED                                 NOT NULL DEFAULT '0',
  `status_delivery` TINYINT(1) UNSIGNED                                 NOT NULL DEFAULT '0',
# Needed prices
  `product_price`   DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
  `discount_price`  DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
  `shipping_price`  DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
  `packing_price`   DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
  `vat_price`       DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
  `total_price`     DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
  `paid_price`      DECIMAL(16, 2)                                      NOT NULL DEFAULT '0.00',
# Checkout
  `gateway`         VARCHAR(64)                                         NOT NULL DEFAULT 'offline',
  `delivery`        INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `location`        INT(10) UNSIGNED                                    NOT NULL DEFAULT '0',
  `packing`         TINYINT(1) UNSIGNED                                 NOT NULL DEFAULT '0',
# promos as gift
  `promo_type`      VARCHAR(64)                                         NOT NULL DEFAULT '',
  `promo_value`     VARCHAR(64)                                         NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
);

CREATE TABLE `{basket}` (
  `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order`          INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `product`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `product_price`  DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `discount_price` DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `shipping_price` DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `packing_price`  DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `vat_price`      DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `total_price`    DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `number`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `extra`          TEXT,
  PRIMARY KEY (`id`),
  KEY `order` (`order`),
  KEY `product` (`product`)
);

CREATE TABLE `{invoice}` (
  `id`             INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `random_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `order`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `uid`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip`             CHAR(15)            NOT NULL DEFAULT '',
  `code`           VARCHAR(16)         NOT NULL DEFAULT '',
  `product_price`  DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `discount_price` DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `shipping_price` DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `packing_price`  DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `vat_price`      DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `total_price`    DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `paid_price`     DECIMAL(16, 2)      NOT NULL DEFAULT '0.00',
  `credit_price`   DECIMAL(16, 8)      NOT NULL DEFAULT '0.00',
  `gateway`        VARCHAR(64)         NOT NULL DEFAULT 'offline',
  `status`         TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `time_create`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_duedate`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_payment`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_cancel`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `back_url`       VARCHAR(255)        NOT NULL DEFAULT '',
  `extra`          TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `random_id` (`random_id`),
  KEY `order` (`order`),
  KEY `gateway` (`gateway`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `uid_status` (`uid`, `status`),
  KEY `time_create` (`time_create`),
  KEY `time_duedate` (`time_duedate`),
  KEY `id_time_create` (`id`, `time_create`),
  KEY `id_time_duedate` (`id`, `time_duedate`)
);

CREATE TABLE `{processing}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip`          CHAR(15)         NOT NULL DEFAULT '',
  `invoice`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `random_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `gateway`     VARCHAR(64)      NOT NULL DEFAULT '',
  `time_create` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `random_id` (`random_id`),
  KEY `uid` (`uid`),
  KEY `invoice` (`invoice`),
  KEY `ip` (`ip`)
);

CREATE TABLE `{customer}` (
  `id`           INT(10) UNSIGNED              NOT NULL AUTO_INCREMENT,
  `uid`          INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `ip`           CHAR(15)                      NOT NULL DEFAULT '',
  `id_number`    VARCHAR(255)                  NOT NULL DEFAULT '',
  `first_name`   VARCHAR(255)                  NOT NULL DEFAULT '',
  `last_name`    VARCHAR(255)                  NOT NULL DEFAULT '',
  `email`        VARCHAR(64)                   NOT NULL DEFAULT '',
  `phone`        VARCHAR(16)                   NOT NULL DEFAULT '',
  `mobile`       VARCHAR(16)                   NOT NULL DEFAULT '',
  `address1`     TEXT,
  `address2`     TEXT,
  `address_type` ENUM('delivery', 'invoicing') NOT NULL DEFAULT 'delivery',
  `country`      VARCHAR(64)                   NOT NULL DEFAULT '',
  `state`        VARCHAR(64)                   NOT NULL DEFAULT '',
  `city`         VARCHAR(64)                   NOT NULL DEFAULT '',
  `zip_code`     VARCHAR(16)                   NOT NULL DEFAULT '',
  `company`      VARCHAR(255)                  NOT NULL DEFAULT '',
  `company_id`   VARCHAR(255)                  NOT NULL DEFAULT '',
  `company_vat`  VARCHAR(255)                  NOT NULL DEFAULT '',
  `user_note`    TEXT,
  `time_create`  INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `time_update`  INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `status`       TINYINT(1) UNSIGNED           NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `time_create` (`time_create`)
);

CREATE TABLE `{gateway}` (
  `id`          INT(10) UNSIGNED          NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)              NOT NULL DEFAULT '',
  `path`        VARCHAR(64)               NOT NULL DEFAULT '',
  `description` TEXT,
  `image`       VARCHAR(255)              NOT NULL DEFAULT '',
  `status`      TINYINT(1) UNSIGNED       NOT NULL DEFAULT '0',
  `type`        ENUM('online', 'offline') NOT NULL DEFAULT 'online',
  `option`      TEXT,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
);

CREATE TABLE `{delivery}` (
  `id`     INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `title`  VARCHAR(255)        NOT NULL DEFAULT '',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `status` (`status`)
);

CREATE TABLE `{location}` (
  `id`     INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `parent` INT(5) UNSIGNED     NOT NULL DEFAULT '0',
  `title`  VARCHAR(255)        NOT NULL DEFAULT '',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `title` (`title`),
  KEY `status` (`status`)
);

CREATE TABLE `{delivery_gateway}` (
  `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery` INT(5) UNSIGNED  NOT NULL DEFAULT '0',
  `gateway`  VARCHAR(64)      NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `delivery` (`delivery`),
  KEY `gateway` (`gateway`),
  KEY `delivery_gateway` (`delivery`, `gateway`)
);

CREATE TABLE `{location_delivery}` (
  `id`            INT(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `location`      INT(5) UNSIGNED       NOT NULL DEFAULT '0',
  `delivery`      INT(5) UNSIGNED       NOT NULL DEFAULT '0',
  `price`         DECIMAL(16, 2)        NOT NULL DEFAULT '0.00',
  `delivery_time` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `location` (`location`),
  KEY `delivery` (`delivery`),
  KEY `location_delivery` (`location`, `delivery`)
);

CREATE TABLE `{log}` (
  `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uid`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `invoice`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `gateway`     VARCHAR(64)         NOT NULL DEFAULT '',
  `time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `amount`      DOUBLE(16, 2)       NOT NULL DEFAULT '0.00',
  `authority`   VARCHAR(255)        NOT NULL DEFAULT '',
  `status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `ip`          CHAR(15)            NOT NULL DEFAULT '',
  `value`       TEXT,
  `message`     VARCHAR(255)        NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `ip` (`ip`)
);
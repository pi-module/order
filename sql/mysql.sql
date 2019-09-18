CREATE TABLE `{order}` (
  `id`              INT(10) UNSIGNED            NOT NULL AUTO_INCREMENT,
  `uid`             INT(10) UNSIGNED            NOT NULL DEFAULT '0',
  `code`            VARCHAR(16)                 NOT NULL DEFAULT '',
  `type_commodity`  ENUM ('product', 'service', 'booking') NOT NULL DEFAULT 'product',
  `default_gateway` VARCHAR(64)                 NOT NULL,
  `can_pay`         TINYINT(1) UNSIGNED         NOT NULL DEFAULT '1',
  `plan`            INT(10) UNSIGNED            NOT NULL DEFAULT '0',
  `ip`              CHAR(15)                    NOT NULL DEFAULT '',
  `user_note`       TEXT,
  `admin_note`      TEXT,
  `time_create`     INT(10) UNSIGNED            NOT NULL DEFAULT '0',
  `time_order`      INT(10) UNSIGNED            NOT NULL DEFAULT '0',
  `time_delivery`   INT(10) UNSIGNED            NOT NULL DEFAULT '0',
  `status_order`    TINYINT(1) UNSIGNED         NOT NULL DEFAULT '0',
  `status_delivery` TINYINT(1) UNSIGNED         NOT NULL DEFAULT '0',
  `packing`         TINYINT(1) UNSIGNED         NOT NULL DEFAULT '0',
  `promotion_type`  VARCHAR(64)                 NOT NULL DEFAULT '',
  `promotion_value` VARCHAR(64)                 NOT NULL DEFAULT '',
  `create_by`       ENUM ('ADMIN', 'USER')      NOT NULL DEFAULT 'USER',
  `extra`           TEXT,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
);

CREATE TABLE `{detail}` (
  `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order`          INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `module`         VARCHAR(64)      NOT NULL DEFAULT '',
  `product_type`   VARCHAR(64)      NOT NULL DEFAULT '',
  `product`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time_create`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time_start`     INT(10) UNSIGNED NULL     DEFAULT '0',
  `time_end`       INT(10) UNSIGNED NULL     DEFAULT '0',
  `product_price`  DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `discount_price` DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `shipping_price` DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `packing_price`  DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `setup_price`    DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `vat_price`      DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `number`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `extra`          TEXT,
  `admin_note`     VARCHAR(511)     NULL     DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order` (`order`),
  KEY `product` (`product`)
);

CREATE TABLE `{invoice}` (
  `id`           INT(10) UNSIGNED                                     NOT NULL AUTO_INCREMENT,
  `random_id`    INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `type_payment` ENUM ('free', 'onetime', 'recurring', 'installment') NOT NULL DEFAULT 'onetime',
  `order`        INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `code`         VARCHAR(16)                                          NOT NULL DEFAULT '',
  `type`         ENUM ('NORMAL', 'CREDIT')                            NOT NULL DEFAULT 'NORMAL',
  `status`       TINYINT(1) UNSIGNED                                  NOT NULL DEFAULT '0',
  `time_create`  INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `time_cancel`  INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `time_invoice` INT(10) UNSIGNED                                     NOT NULL DEFAULT '0',
  `back_url`     VARCHAR(255)                                         NOT NULL DEFAULT '',
  `create_by`    ENUM ('ADMIN', 'USER')                               NOT NULL DEFAULT 'USER',
  `extra`        TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `random_id` (`random_id`),
  KEY `order` (`order`),
  KEY `status` (`status`),
  KEY `time_create` (`time_create`),
  KEY `id_time_create` (`id`, `time_create`)
);

CREATE TABLE `{processing}` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid`         INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip`          CHAR(15)         NOT NULL DEFAULT '',
  `order`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `random_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `gateway`     VARCHAR(64)      NOT NULL DEFAULT '',
  `time_create` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `random_id` (`random_id`),
  KEY `uid` (`uid`),
  KEY `order` (`order`),
  KEY `ip` (`ip`)
);

CREATE TABLE `{customer_address}` (
  `id`                  INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uid`                 INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip`                  CHAR(15)            NOT NULL DEFAULT '',
  `id_number`           VARCHAR(255)        NOT NULL DEFAULT '',
  `first_name`          VARCHAR(255)        NOT NULL DEFAULT '',
  `last_name`           VARCHAR(255)        NOT NULL DEFAULT '',
  `email`               VARCHAR(64)         NOT NULL DEFAULT '',
  `phone`               VARCHAR(16)         NOT NULL DEFAULT '',
  `mobile`              VARCHAR(16)         NOT NULL DEFAULT '',
  `address1`            TEXT,
  `address2`            TEXT,
  `country`             VARCHAR(64)         NOT NULL DEFAULT '',
  `state`               VARCHAR(64)         NOT NULL DEFAULT '',
  `city`                VARCHAR(64)         NOT NULL DEFAULT '',
  `zip_code`            VARCHAR(16)         NOT NULL DEFAULT '',
  `company`             VARCHAR(255)        NOT NULL DEFAULT '',
  `company_id`          VARCHAR(255)        NOT NULL DEFAULT '',
  `company_vat`         VARCHAR(255)        NOT NULL DEFAULT '',
  `user_note`           TEXT,
  `time_create`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_update`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `status`              TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `delivery`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `location`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `delivery_favourite`  TINYINT(1) UNSIGNED,
  `invoicing_favourite` TINYINT(1) UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `time_create` (`time_create`)
);

CREATE TABLE `{gateway}` (
  `id`          INT(10) UNSIGNED           NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)               NOT NULL DEFAULT '',
  `path`        VARCHAR(64)                NOT NULL DEFAULT '',
  `description` TEXT,
  `image`       VARCHAR(255)               NOT NULL DEFAULT '',
  `status`      TINYINT(1) UNSIGNED        NOT NULL DEFAULT '0',
  `type`        ENUM ('online', 'offline') NOT NULL DEFAULT 'online',
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
  `order`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
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

CREATE TABLE `{credit}` (
  `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid`           INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time_update`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `amount`        DECIMAL(16, 2)   NOT NULL DEFAULT '0.00',
  `amount_detail` VARCHAR(255)     NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
);

CREATE TABLE `{credit_history}` (
  `id`                 INT(10) UNSIGNED              NOT NULL AUTO_INCREMENT,
  `uid`                INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `time_create`        INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `order`              INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `invoice`            INT(10) UNSIGNED              NOT NULL DEFAULT '0',
  `amount`             DECIMAL(16, 2)                NOT NULL DEFAULT '0.00',
  `amount_old`         DECIMAL(16, 2)                NOT NULL DEFAULT '0.00',
  `amount_new`         DECIMAL(16, 2)                NOT NULL DEFAULT '0.00',
  `status`             TINYINT(1) UNSIGNED           NOT NULL DEFAULT '0',
  `status_fluctuation` ENUM ('increase', 'decrease') NOT NULL DEFAULT 'increase',
  `status_action`      ENUM ('automatic', 'manual')  NOT NULL DEFAULT 'automatic',
  `message_user`       TEXT,
  `message_admin`      TEXT,
  `ip`                 CHAR(15)                      NOT NULL DEFAULT '',
  `module`             VARCHAR(64)                   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time_create` (`time_create`),
  KEY `order` (`order`),
  KEY `invoice` (`invoice`)
);

CREATE TABLE `{promocode}` (
  `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `code`       VARCHAR(16)         NOT NULL,
  `promo`      TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `time_start` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time_end`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `module`     VARCHAR(255)        NOT NULL,
  `showcode`   tinyint(1)          NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `{order_address}` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order`       int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_number`   VARCHAR(255)     NOT NULL DEFAULT '',
  `type`        ENUM ('DELIVERY', 'INVOICING'),
  `first_name`  varchar(255)     NOT NULL DEFAULT '',
  `last_name`   varchar(255)     NOT NULL DEFAULT '',
  `email`       varchar(64)      NOT NULL DEFAULT '',
  `phone`       varchar(16)      NOT NULL DEFAULT '',
  `mobile`      varchar(16)      NOT NULL DEFAULT '',
  `address1`    text,
  `address2`    text,
  `country`     varchar(64)      NOT NULL DEFAULT '',
  `state`       varchar(64)      NOT NULL DEFAULT '',
  `city`        varchar(64)      NOT NULL DEFAULT '',
  `zip_code`    varchar(16)      NOT NULL DEFAULT '',
  `company`     varchar(255)     NOT NULL DEFAULT '',
  `company_id`  varchar(255)     NOT NULL DEFAULT '',
  `company_vat` varchar(255)     NOT NULL DEFAULT '',
  `delivery`    int(10) UNSIGNED NOT NULL DEFAULT '0',
  `location`    int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
);

CREATE TABLE `{installment}` (
  `id`         int(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
  `number`     smallint(3) UNSIGNED NOT NULL DEFAULT '1',
  `commission` DECIMAL(16, 2)       NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `{installment_product}` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `installment`  int(10) UNSIGNED NOT NULL,
  `module`       VARCHAR(64)      NOT NULL DEFAULT '',
  `product_type` VARCHAR(64)      NOT NULL DEFAULT '',
  `product`      int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `product_type` (`product_type`),
  KEY `product` (`product`)
);

CREATE TABLE `{invoice_installment}` (
  `id`             int(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
  `invoice`        int(10) UNSIGNED     NOT NULL DEFAULT '0',
  `count`          smallint(3) UNSIGNED NOT NULL DEFAULT '1',
  `gateway`        VARCHAR(64)          NOT NULL DEFAULT 'offline',
  `status_payment` TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',
  `time_payment`   INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `time_duedate`   INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `due_price`      DECIMAL(16, 2)       NOT NULL DEFAULT '0.00',
  `credit_price`   DECIMAL(16, 8)       NOT NULL DEFAULT '0.00',
  `comment`        TEXT,
  PRIMARY KEY (`id`),
  KEY `invoice` (`invoice`)
);
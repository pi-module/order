CREATE TABLE `{order}` (
    # General
    `id` int(10) unsigned NOT NULL auto_increment,
    `uid` int(10) unsigned NOT NULL default '0',
    `code` varchar(16) NOT NULL default '',
    `type` enum('free','onetime','recurring','installment') NOT NULL default 'onetime',
    # Module
    `module_name` varchar(64) NOT NULL default '',
    `module_table` varchar(64) NOT NULL default '',
    `module_item` int(10) unsigned NOT NULL default '0',
    # Customer information
    `ip` char(15) NOT NULL default '',
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
    # Notes
    `user_note` text,
    `admin_note` text,
    # Needed times
    `time_create` int(10) unsigned NOT NULL default '0',
    `time_payment` int(10) unsigned NOT NULL default '0',
    `time_delivery` int(10) unsigned NOT NULL default '0',
    `time_finish` int(10) unsigned NOT NULL default '0',
    `time_start` int(10) unsigned NOT NULL default '0',
    `time_end` int(10) unsigned NOT NULL default '0',
    # Needed status
    `status_order` tinyint(1) unsigned NOT NULL default '0',
    `status_payment` tinyint(1) unsigned NOT NULL default '0',
    `status_delivery` tinyint(1) unsigned NOT NULL default '0',
    # Needed prices
    `product_price` decimal(16,2) NOT NULL default '0.00',
    `discount_price` decimal(16,2) NOT NULL default '0.00',
    `shipping_price` decimal(16,2) NOT NULL default '0.00',
    `packing_price` decimal(16,2) NOT NULL default '0.00',
    `vat_price` decimal(16,2) NOT NULL default '0.00',
    `total_price` decimal(16,2) NOT NULL default '0.00',
    `paid_price` decimal(16,2) NOT NULL default '0.00',
    # Checkout
    `gateway` varchar(64) NOT NULL default 'offline',
    `delivery` int(10) unsigned NOT NULL default '0',
    `location` int(10) unsigned NOT NULL default '0',
    `packing` tinyint(1) unsigned NOT NULL default '0',
    # promos as gift
    `promo_type` varchar(64) NOT NULL default '',
    `promo_value` varchar(64) NOT NULL default '',
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`)
);

CREATE TABLE `{basket}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `order` int(10) unsigned NOT NULL default '0',
    `product` int(10) unsigned NOT NULL default '0',
    `product_price` decimal(16,2) NOT NULL default '0.00',
    `discount_price` decimal(16,2) NOT NULL default '0.00',
    `shipping_price` decimal(16,2) NOT NULL default '0.00',
    `packing_price` decimal(16,2) NOT NULL default '0.00',
    `vat_price` decimal(16,2) NOT NULL default '0.00',
    `number` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY (`id`),
    KEY `order` (`order`),
    KEY `product` (`product`)
);

CREATE TABLE `{invoice}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `random_id` int(10) unsigned NOT NULL default '0',
    `order` int(10) unsigned NOT NULL default '0',
    `uid` int(10) unsigned NOT NULL default '0',
    `ip` char(15) NOT NULL default '',
    `product_price` decimal(16,2) NOT NULL default '0.00',
    `discount_price` decimal(16,2) NOT NULL default '0.00',
    `shipping_price` decimal(16,2) NOT NULL default '0.00',
    `packing_price` decimal(16,2) NOT NULL default '0.00',
    `vat_price` decimal(16,2) NOT NULL default '0.00',
    `total_price` decimal(16,2) NOT NULL default '0.00',
    `paid_price` decimal(16,2) NOT NULL default '0.00',
    `gateway` varchar(64) NOT NULL default 'offline',
    `status` tinyint(1) unsigned NOT NULL default '0',
    `time_create` int(10) unsigned NOT NULL default '0',
    `time_duedate` int(10) unsigned NOT NULL default '0',
    `time_payment` int(10) unsigned NOT NULL default '0',
    `time_cancel` int(10) unsigned NOT NULL default '0',
    `back_url` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`id`),
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
    `id` int(10) unsigned NOT NULL auto_increment,
    `uid` int(10) unsigned NOT NULL default '0',
    `ip` char(15) NOT NULL default '',
    `invoice` int(10) unsigned NOT NULL default '0',
    `random_id` int(10) unsigned NOT NULL default '0',
    `gateway` varchar(64) NOT NULL default '',
    `time_create` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`),
    UNIQUE KEY `random_id` (`random_id`),
    KEY `uid` (`uid`),
    KEY `invoice` (`invoice`),
    KEY `ip` (`ip`)
);

CREATE TABLE `{gateway}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `title` varchar(255) NOT NULL default '',
    `path` varchar(64) NOT NULL default '',
    `description` text,
    `image` varchar(255) NOT NULL default '',
    `status` tinyint(1) unsigned NOT NULL default '0',
    `type` enum('online','offline') NOT NULL default 'online',
    `option` text,
    PRIMARY KEY  (`id`),
    KEY `status` (`status`)
);

CREATE TABLE `{delivery}` (
    `id` int (10) unsigned NOT NULL auto_increment,
    `title` varchar(255) NOT NULL default '',
    `status` tinyint(1) unsigned NOT NULL default '1',
    PRIMARY KEY (`id`),
    KEY `title` (`title`),
    KEY `status` (`status`)
);

CREATE TABLE `{location}` (
    `id` int (10) unsigned NOT NULL auto_increment,
    `parent` int(5) unsigned NOT NULL default '0',
    `title` varchar(255) NOT NULL default '',
    `status` tinyint(1) unsigned NOT NULL default '1',
    PRIMARY KEY (`id`),
    KEY `parent` (`parent`),
    KEY `title` (`title`),
    KEY `status` (`status`)
);

CREATE TABLE `{delivery_gateway}` (
    `id` int (10) unsigned NOT NULL auto_increment,
    `delivery` int(5) unsigned NOT NULL default '0',
    `gateway` varchar(64) NOT NULL default '',
    PRIMARY KEY (`id`),
    KEY `delivery` (`delivery`),
    KEY `gateway` (`gateway`),
    KEY `delivery_gateway` (`delivery`, `gateway`)
);

CREATE TABLE `{location_delivery}` (
    `id` int (10) unsigned NOT NULL auto_increment,
    `location` int(5) unsigned NOT NULL default '0',
    `delivery` int(5) unsigned NOT NULL default '0',
    `price` decimal(16,2) NOT NULL default '0.00',
    `delivery_time` mediumint(8) unsigned NOT NULL default '0',
    PRIMARY KEY (`id`),
    KEY `location` (`location`),
    KEY `delivery` (`delivery`),
    KEY `location_delivery` (`location`, `delivery`)
);

CREATE TABLE `{log}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `uid` int(10) unsigned NOT NULL default '0',
    `invoice` int(10) unsigned NOT NULL default '0',
    `gateway` varchar(64) NOT NULL default '',
    `time_create` int(10) unsigned NOT NULL default '0',
    `amount` double(16,2) NOT NULL default '0.00',
    `authority` varchar(255) NOT NULL default '',
    `status` tinyint(1) unsigned NOT NULL default '0',
    `ip` char(15) NOT NULL default '',
    `value` text,
    `message` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`id`),
    KEY `uid` (`uid`),
    KEY `ip` (`ip`)
);
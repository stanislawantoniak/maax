<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_GROUPEDPRODUCTPROMOTIONS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

  

$this->startSetup();

$this->run("

create table if not exists {$this->getTable('itoris_groupedproductpromotions_rules_view')} (
	`view_id` int unsigned not null auto_increment primary key,
	`scope` enum('default', 'website', 'store') not null,
	`scope_id` int unsigned not null,
	unique(`scope`, `scope_id`)
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_groupedproductpromotions_rules_settings')} (
	`setting_id` int unsigned not null auto_increment primary key,
	`view_id` int unsigned not null,
	`product_id` int(10) unsigned null,
	`key` varchar(255) not null,
	`value` int unsigned not null,
	`type` enum('text', 'default') null,
	unique(`view_id`, `key`, `product_id`),
	foreign key (`view_id`) references {$this->getTable('itoris_groupedproductpromotions_rules_view')} (`view_id`) on delete cascade on update cascade,
	foreign key (`product_id`) references {$this->getTable('catalog_product_entity')} (`entity_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_groupedproductpromotions_rules_settings_text')} (
	`setting_id` int unsigned not null,
	`value` text not null,
	index(`setting_id`),
	CONSTRAINT `FK_itoris_dpp_rule_settings` foreign key (`setting_id`) references {$this->getTable('itoris_groupedproductpromotions_rules_settings')} (`setting_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_groupedproductpromotions_rules')} (
	`rule_id` int unsigned not null auto_increment primary key,
	`product_id` int(10) unsigned null,
	`title` varchar(255) not null,
	`position` int(10) unsigned null,
	`status` int unsigned not null,
	`active_from` date null,
	`active_to` date null,
	foreign key (`product_id`) references {$this->getTable('catalog_product_entity')} (`entity_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_groupedproductpromotions_rules_group')} (
    `rule_id` int unsigned not null,
    `group_id` smallint(5) unsigned not null,
    foreign key (`rule_id`) references {$this->getTable('itoris_groupedproductpromotions_rules')} (`rule_id`) on delete cascade on update cascade,
    foreign key (`group_id`) references {$this->getTable('customer_group')} (`customer_group_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_groupedproductpromotions_rules_product')} (
    `rule_product_id` int unsigned not null auto_increment primary key,
	`rule_id` int unsigned not null,
	`product_id` int(10) unsigned null,
	`in_set`  int unsigned not null,
	`qty` int unsigned null,
	`discount` float null,
	`type` int unsigned null,
	foreign key (`rule_id`) references {$this->getTable('itoris_groupedproductpromotions_rules')} (`rule_id`) on delete cascade on update cascade,
	foreign key (`product_id`) references {$this->getTable('catalog_product_entity')} (`entity_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;
");

$this->endSetup();
?>
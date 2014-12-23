<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title Advanced Email and SMS Marketing Automation
 * @category Marketing
 * @package Licentia
 * @author Bento Vilas Boas <bento@licentia.pt>
 * @Copyright (c) 2012 Licentia - http://licentia.pt
 * @license Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */


$installer = $this;
$installer->startSetup();

$installer->run("
-- ----------------------------
-- Table structure for `fidelitas_conversions`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_conversions')}`;
CREATE TABLE `{$this->getTable('fidelitas_conversions')}` (
`conversion_id` int(11) NOT NULL AUTO_INCREMENT,
`campaign_id` int(11) DEFAULT NULL,
`subscriber_id` int(11) DEFAULT NULL,
`order_id` int(11) DEFAULT NULL,
`order_date` datetime DEFAULT NULL,
`order_amount` decimal(8,4) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`subscriber_email` varchar(255) DEFAULT NULL,
`subscriber_firstname` varchar(255) DEFAULT NULL,
`subscriber_lastname` varchar(255) DEFAULT NULL,
`campaign_name` varchar(255) DEFAULT NULL,
PRIMARY KEY (`conversion_id`),
KEY `campaign_id` (`campaign_id`),
KEY `subscriber_id` (`subscriber_id`),
CONSTRAINT `FK_CAMPAIGNS` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of conversions from campaigns';

-- ----------------------------
-- Table structure for `fidelitas_coupons`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_coupons')}`;
CREATE TABLE `{$this->getTable('fidelitas_coupons')}` (
`coupon_id` int(11) NOT NULL AUTO_INCREMENT,
`coupon_code` varchar(255) DEFAULT NULL,
`subscriber_email` varchar(255) DEFAULT NULL,
`times_used` int(11) DEFAULT NULL,
`force` enum('0','1')DEFAULT '0',
`rule_id` int(10) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`campaign_id` int(11) DEFAULT NULL,
`used_at` datetime DEFAULT NULL,
`created_at` datetime DEFAULT NULL,
`order_id` int(11) DEFAULT NULL,
PRIMARY KEY (`coupon_id`),
UNIQUE KEY `UNQ_COUPON` (`coupon_code`),
KEY `rule_id` (`rule_id`),
KEY `subscriber_email` (`subscriber_email`),
KEY `coupon_code` (`coupon_code`),
KEY `campaign_id` (`campaign_id`),
CONSTRAINT `FK_CAMPAIGNS_COUPONS` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='E-Goi - Coupons list for widget';


-- ----------------------------
-- Table structure for `fidelitas_history`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_history')}`;
CREATE TABLE `{$this->getTable('fidelitas_history')}` (
`history_id` int(11) NOT NULL AUTO_INCREMENT,
`campaign_id` int(11) DEFAULT NULL,
`subscriber_email` varchar(11) DEFAULT NULL,
`sent_at` datetime DEFAULT NULL,
`childrend_id` int(11) DEFAULT NULL,
`subscriber_phone` varchar(255) DEFAULT NULL,
PRIMARY KEY (`history_id`),
KEY `subscriber_phone` (`subscriber_phone`),
KEY `subscriber_email` (`subscriber_email`),
KEY `campaign_id` (`campaign_id`),
CONSTRAINT `FK_CAMPAIGNS_HISTORY` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='E-Goi - Log sending history for recurring campaigns and unique receivers';

-- ----------------------------
-- Table structure for `fidelitas_coupons`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_conversions_segments')}`;
CREATE TABLE `{$this->getTable('fidelitas_conversions_segments')}` (
`conversion_id` int(11) NOT NULL AUTO_INCREMENT,
`campaign_id` int(11) DEFAULT NULL,
`segment_id` int(11) DEFAULT NULL,
`subscriber_id` int(11) DEFAULT NULL,
  `listnum` int(11) DEFAULT NULL,
`order_id` int(11) DEFAULT NULL,
`order_date` datetime DEFAULT NULL,
`order_amount` decimal(8,4) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`subscriber_email` varchar(255) DEFAULT NULL,
`subscriber_firstname` varchar(255) DEFAULT NULL,
`subscriber_lastname` varchar(255) DEFAULT NULL,
`campaign_name` varchar(255) DEFAULT NULL,
PRIMARY KEY (`conversion_id`),
KEY `campaign_id` (`campaign_id`),
KEY `subscriber_id` (`subscriber_id`),
KEY `listnum` (`listnum`),
KEY `segment_id` (`segment_id`),
CONSTRAINT `FK_CAMPAIGNS_CONVERSIONS` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_SEGMENTS_CONVERSIONS` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of segments conversions from campaigns';


-- ----------------------------
-- Table structure for `{$this->getTable('fidelitas_segments_evolutions_summary')}`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_segments_evolutions_summary')}`;
CREATE TABLE `{$this->getTable('fidelitas_segments_evolutions_summary')}` (
`summary_id` int(11) NOT NULL AUTO_INCREMENT,
`list_id` int(11) DEFAULT NULL,
`segment_id` int(11) DEFAULT NULL,
`campaign_id` int(11) DEFAULT NULL,
`records` int(11) DEFAULT NULL,
`conversions_number` int(11) DEFAULT NULL,
`unique_conversions` int(11) DEFAULT NULL,
`conversions_amount` decimal(10,4) DEFAULT NULL,
`conversions_average` decimal(10,4) DEFAULT NULL,
`created_at` date DEFAULT NULL,
`change` int(11) DEFAULT NULL,
PRIMARY KEY (`summary_id`)) ENGINE=`InnoDB` COMMENT='E-Goi - Summary Conversions' CHECKSUM=0 DELAY_KEY_WRITE=0;


-- ----------------------------
--  Table structure for `fidelitas_groups`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_groups')}`;
CREATE TABLE `{$this->getTable('fidelitas_groups')}` (
  `change_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `subscriber_id` int(11) DEFAULT NULL,
  `subscriber_email` varchar(255) DEFAULT NULL,
  `subscriber_firstname` varchar(255) DEFAULT NULL,
  `subscriber_lastname` varchar(255) DEFAULT NULL,
  `change_date` datetime DEFAULT NULL,
  `new_group` smallint(5) unsigned DEFAULT NULL,
  `previous_group` smallint(5) unsigned DEFAULT NULL,
  `segment_id` int(11) DEFAULT NULL,
  `operation` enum('added','reverted') DEFAULT NULL,
  PRIMARY KEY (`change_id`),
  KEY `new_group` (`new_group`),
  KEY `previous_group` (`previous_group`),
  KEY `subscriber_id` (`subscriber_id`),
  KEY `segment_id` (`segment_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `FK_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_GROUP_PREVIOUS` FOREIGN KEY (`previous_group`) REFERENCES `{$this->getTable('customer_group')}` (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_GROUP_NEW` FOREIGN KEY (`new_group`) REFERENCES `{$this->getTable('customer_group')}` (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_SEGMENT` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Customer Group Changes Logs';



-- ----------------------------
-- Table structure for `{$this->getTable('fidelitas_segments_evolutions')}`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_segments_evolutions')}`;
CREATE TABLE `{$this->getTable('fidelitas_segments_evolutions')}` (
`record_id` int(11) NOT NULL AUTO_INCREMENT,
`list_id` int(11) DEFAULT NULL,
`segment_id` int(11) NOT NULL,
`campaign_id` int(11) DEFAULT NULL,
`subscriber_id` int(11) DEFAULT NULL,
`first_name` varchar(255) DEFAULT NULL,
`last_name` varchar(255) DEFAULT NULL,
`email` varchar(255) DEFAULT NULL,
`cellphone` varchar(255) DEFAULT NULL,
`listnum` int(11) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`conversions_number` int(11) DEFAULT NULL,
`conversions_amount` decimal(10,4) DEFAULT NULL,
`conversions_average` decimal(10,4) DEFAULT NULL,
`created_at` date DEFAULT NULL,
PRIMARY KEY (`record_id`),
INDEX `email_index` (`email`),
INDEX `segment_id` (`segment_id`) )
ENGINE=`InnoDB` COMMENT='E-Goi - Tmp list of segment subscribers'

");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_reports')}` ADD COLUMN `campaign_url` varchar(255)DEFAULT NULL ");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `run` varchar(20) AFTER `description` ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `conversions_number` int ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `conversions_amount` decimal(10,4)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `conversions_average` decimal(10,4)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `cron` enum('0','d','w','m') DEFAULT '0'");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `cron_last_run` date DEFAULT NULL");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `build` tinyint(4) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `last_update` datetime DEFAULT NULL");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `notify_user` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `change_group` enum('0','d','w','m')");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `group_id` smallint(5) UNSIGNED");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `cron_group_last_run` date DEFAULT NULL");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `cron_priority` smallint(5)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `preserve_groups` varchar(255) ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `operation` enum('added','reverted')");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers')}`ADD COLUMN `subscriber_email` varchar(255)");


$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` ADD COLUMN `conversions_number` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` ADD COLUMN `conversions_amount` decimal(10,4) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` ADD COLUMN `conversions_average` decimal(10,4) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` ADD INDEX `status` (`status`) ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` CHANGE COLUMN `bounces` `bounces` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` CHANGE COLUMN `email_sent` `email_sent` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` CHANGE COLUMN `bounces` `bounces` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` CHANGE COLUMN `email_views` `email_views` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` CHANGE COLUMN `referrals` `referrals` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` CHANGE COLUMN `referrals_converted` `referrals_converted` int(11) DEFAULT NULL ");


$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `conversions_number` int(11) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `conversions_amount` decimal(10,4) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `conversions_average` decimal(10,4) DEFAULT NULL ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `recurring_unique` enum('0','1') NOT NULL DEFAULT '0' ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD INDEX `parent_id` (`parent_id`) ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD INDEX `hidden` (`hidden`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD INDEX `status` (`status`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD INDEX `local_status` (`local_status`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD INDEX `recurring` (`recurring`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD INDEX `recurring_next_run` (`recurring_next_run`)");


$installer->run("ALTER TABLE `{$this->getTable('fidelitas_lists')}` ADD INDEX `store_id` (`store_id`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_lists')}` ADD INDEX `purpose` (`purpose`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_lists')}` ADD INDEX `is_active` (`is_active`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_lists')}` ADD INDEX `listnum` (`listnum`)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_lists')}` CHANGE COLUMN `listnum` `listnum` int(12) DEFAULT NULL after `list_id`");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers_list')}`ADD COLUMN `conversions_amount` decimal(10,4)  ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers_list')}` ADD COLUMN `conversions_average` decimal(10,4) ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers_list')}`ADD COLUMN `conversions_number` int ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_segments_subscribers_list')}` ADD INDEX `segment_id` (`segment_id`)");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COMMENT='E-Goi - API calls logs' ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `section` `section` enum('subscribers','lists','campaigns') CHARACTER SET utf8 NOT NULL after `log_id`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `action` `action` varchar(255) CHARACTER SET utf8 DEFAULT '' after `request_date`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `result` `result` text CHARACTER SET utf8 DEFAULT NULL after `action`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `exception` `exception` enum('0','1') CHARACTER SET utf8 DEFAULT '0' after `result`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `additional_data` `additional_data` text CHARACTER SET utf8 DEFAULT NULL after `exception`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `params` `params` text CHARACTER SET utf8 DEFAULT NULL after `additional_data`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_logs')}` CHANGE COLUMN `summary` `summary` varchar(255) CHARACTER SET utf8 DEFAULT NULL after `params`");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_reports')}` ADD CONSTRAINT `FK_CAMPAIGNS_REPORTS` FOREIGN KEY (`hash`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`hash`) ON UPDATE CASCADE ON DELETE CASCADE");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_subscribers')}` ADD CONSTRAINT `FK_SUBSCRIBERS_LISTS` FOREIGN KEY (`list`) REFERENCES `{$this->getTable('fidelitas_lists')}` (`listnum`) ON UPDATE CASCADE ON DELETE CASCADE");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD CONSTRAINT `FK_LISTS_LIST` FOREIGN KEY (`listnum`) REFERENCES `{$this->getTable('fidelitas_lists')}` (`listnum`) ON UPDATE CASCADE ON DELETE CASCADE");


$installer->endSetup();

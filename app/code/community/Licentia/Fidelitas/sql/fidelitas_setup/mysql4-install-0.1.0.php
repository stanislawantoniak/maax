<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */


$installer = $this;
$installer->startSetup();

$installer->run("
-- ----------------------------
--  Table structure for `fidelitas_account`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_account')}`;
CREATE TABLE `{$this->getTable('fidelitas_account')}`(
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL DEFAULT '0',
  `company_name` varchar(255) DEFAULT NULL,
  `company_legal_name` varchar(255) DEFAULT NULL,
  `company_type` varchar(255) DEFAULT NULL,
  `business_activity_code` varchar(255) DEFAULT NULL,
  `date_registration` date DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `signup_date` date DEFAULT NULL,
  `credits` float(8,2) DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Account Info';


-- ----------------------------
--  Table structure for `fidelitas_campaigns`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_campaigns')}`;
CREATE TABLE `{$this->getTable('fidelitas_campaigns')}`(
  `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `lists_ids` varchar(255) DEFAULT NULL,
  `listnum` int(11) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `from` varchar(255) DEFAULT NULL,
  `internal_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `deploy_at` datetime DEFAULT NULL,
  `channel` enum('Email','SMS')DEFAULT 'Email',
  `status` varchar(255) DEFAULT 'standby',
  `processed_messages` int(11) DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `recurring_month` varchar(255) DEFAULT NULL,
  `recurring_monthly` varchar(255) DEFAULT NULL,
  `recurring_day` varchar(255) DEFAULT NULL,
  `recurring_daily` varchar(255) DEFAULT NULL,
  `recurring_last_run` datetime DEFAULT NULL,
  `recurring_next_run` datetime DEFAULT NULL,
  `recurring_time` int(11) DEFAULT NULL,
  `recurring_first_run` date DEFAULT NULL,
  `run_until` date DEFAULT NULL,
  `run_times` int(11) DEFAULT NULL,
  `run_times_left` int(11) DEFAULT NULL,
  `recurring` varchar(2) DEFAULT NULL,
  `segments_ids` varchar(255) DEFAULT NULL,
  `actions_id` int(11) DEFAULT NULL,
  `auto` enum('0','1')  NOT NULL DEFAULT '0',
  `local_status` enum('standby','finished','sent','running','error') NOT NULL DEFAULT 'standby',
  `segments_options` enum('merge','intersect') NOT NULL DEFAULT 'merge',
  `hidden` enum('0','1') NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `is_active` enum('0','1') NOT NULL DEFAULT '1',
  `end_method` enum('run_until','number','both') NOT NULL DEFAULT 'run_until',
  `link_referer_top` tinyint(4) DEFAULT NULL,
  `link_referer_bottom` tinyint(4) DEFAULT NULL,
  `link_view_top` tinyint(4) DEFAULT NULL,
  `link_view_bottom` tinyint(4) DEFAULT NULL,
  `link_remove_top` tinyint(4) DEFAULT NULL,
  `link_remove_bottom` tinyint(4) DEFAULT NULL,
  `link_edit_top` tinyint(4) DEFAULT NULL,
  `link_edit_bottom` tinyint(4) DEFAULT NULL,
  `link_print_top` tinyint(4) DEFAULT NULL,
  `link_print_bottom` tinyint(4) DEFAULT NULL,
  `link_social_networks_top` tinyint(4) DEFAULT NULL,
  `link_social_networks_bottom` tinyint(4) DEFAULT NULL,
  `service_response` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`campaign_id`),
  KEY `hash_i` (`hash`),
  KEY `listnum_i` (`listnum`),
  KEY `channel_i` (`channel`),
  KEY `recurring_i` (`recurring`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of campaigns';

-- ----------------------------
--  Table structure for `fidelitas_cron_report`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_cron_report')}`;
CREATE TABLE `{$this->getTable('fidelitas_cron_report')}`(
  `cron_id` int(11) NOT NULL AUTO_INCREMENT,
  `scope` varchar(255)  DEFAULT NULL,
  `value` varchar(255)  DEFAULT NULL,
  `period` date DEFAULT NULL,
  `status` varchar(11)  DEFAULT NULL,
  PRIMARY KEY (`cron_id`),
  KEY `period_i` (`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Cron for administrative notifications';

-- ----------------------------
--  Table structure for `fidelitas_lists`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_lists')}`;
CREATE TABLE `{$this->getTable('fidelitas_lists')}`(
  `list_id` int(11) NOT NULL AUTO_INCREMENT,
  `listnum` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `internal_name` varchar(255) DEFAULT NULL,
  `subs_activos` int(11) DEFAULT NULL,
  `subs_total` int(11) DEFAULT NULL,
  `canal_email` enum('0','1')  NOT NULL DEFAULT '1',
  `canal_sms` enum('0','1')  NOT NULL DEFAULT '0',
  `store_id` int(11) DEFAULT NULL,
  `is_active` enum('0','1') DEFAULT '1',
  `purpose` enum('regular','admin','client','auto') NOT NULL DEFAULT 'regular',
  PRIMARY KEY (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of Lists';


-- ----------------------------
--  Table structure for `fidelitas_logs`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_logs')}`;
CREATE TABLE `{$this->getTable('fidelitas_logs')}`(
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `section` enum('subscribers','lists','campaigns') NOT NULL,
  `request_date` datetime DEFAULT NULL,
  `action` varchar(255) DEFAULT '',
  `result` text,
  `exception` enum('0','1') DEFAULT '0',
  `additional_data` text,
  `params` text,
  `summary` varchar(255) DEFAULT NULL,
  `section_item_id` int DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `section_i` (`section`),
  KEY `action_i` (`action`),
  KEY `exception_i` (`exception`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of logs';


-- ----------------------------
--  Table structure for `fidelitas_reports`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_reports')}`;
CREATE TABLE `{$this->getTable('fidelitas_reports')}`(
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) DEFAULT NULL,
  `sent` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `returned` int(11) DEFAULT NULL,
  `recomendations` int(11) DEFAULT NULL,
  `recomendations_success` int(11) DEFAULT NULL,
  `facebook_like` int(11) DEFAULT NULL,
  `facebook_share` int(11) DEFAULT NULL,
  `google_buzzes` int(11) DEFAULT NULL,
  `tweets` int(11) DEFAULT NULL,
  `other_social` int(11) DEFAULT NULL,
  `clicks_rate` int(11) DEFAULT NULL,
  `total_removes` int(11) DEFAULT NULL,
  `unique_clicks` int(11) DEFAULT NULL,
  `clicks_sub` int(11) DEFAULT NULL,
  `complain_rate` int(11) DEFAULT NULL,
  `complain` int(11) DEFAULT NULL,
  `programs_totals` text,
  `links_campaign` text,
  `unique_links` text,
  `not_opened` int(11) DEFAULT NULL,
  `top_country` text,
  `top_city` text,
  `total_country` int(11) DEFAULT NULL,
  `total_city` int(11) DEFAULT NULL,
  `delivered` int(11) DEFAULT NULL,
  `not_delivered` int(11) DEFAULT NULL,
  `invalid` int(11) DEFAULT NULL,
  `networks` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `unique_views` int(11) DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `hash_i` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of reports of campaigns';

-- ----------------------------
--  Table structure for `fidelitas_segments_subscribers`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_segments_subscribers')}`;
CREATE TABLE `{$this->getTable('fidelitas_segments_subscribers')}`(
  `segment_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `conditions_serialized` text,
  `is_active` enum('0','1')  DEFAULT '1',
  `records` int(11) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of segments';


-- ----------------------------
--  Table structure for `fidelitas_segments_subscribers_list`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_segments_subscribers_list')}`;
CREATE TABLE `{$this->getTable('fidelitas_segments_subscribers_list')}`(
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT NULL,
  `segment_id` int(11) NOT NULL,
  `subscriber_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `cellphone` varchar(255) DEFAULT NULL,
  `listnum` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `email_index`(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of subscribers from segments';


-- ----------------------------
--  Table structure for `fidelitas_senders`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_senders')}`;
CREATE TABLE `{$this->getTable('fidelitas_senders')}`(
  `sender_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` varchar(255) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of Senders';


-- ----------------------------
--  Table structure for `fidelitas_subscribers`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_subscribers')}`;
CREATE TABLE `{$this->getTable('fidelitas_subscribers')}`(
  `subscriber_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `uid` varchar(255) DEFAULT NULL,
  `add_date` date DEFAULT NULL,
  `subscription_method` varchar(255) DEFAULT NULL,
  `list` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `cellphone` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `zip_code` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `id_card` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `birth_date` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `bounces` varchar(255) DEFAULT NULL,
  `email_sent` varchar(255) DEFAULT NULL,
  `email_views` varchar(255) DEFAULT NULL,
  `referrals` varchar(255) DEFAULT NULL,
  `referrals_converted` varchar(255) DEFAULT NULL,
  `clicks` int(11) DEFAULT NULL,
  `sms_sent` int(11) DEFAULT NULL,
  `sms_delivered` int(11) DEFAULT NULL,
  `remove_method` varchar(255) DEFAULT NULL,
  `remove_date` datetime DEFAULT NULL,
  PRIMARY KEY (`subscriber_id`),
  KEY `email_i` (`email`),
  KEY `list_i` (`list`),
  KEY `uid_i` (`uid`),
  KEY `customer_i` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of subscribers';

-- ----------------------------
--  Table structure for `fidelitas_templates`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_templates')}`;
CREATE TABLE `{$this->getTable('fidelitas_templates')}`(
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `message` text,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`template_id`),
  KEY `status_i` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of templates';

-- ----------------------------
--  Table structure for `fidelitas_widget_cache`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_widget_cache')}`;
CREATE TABLE `{$this->getTable('fidelitas_widget_cache')}`(
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `products_ids` text,
  `build_date` datetime DEFAULT NULL,
  `identifier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Cache for wiget';

");

$installer->endSetup();

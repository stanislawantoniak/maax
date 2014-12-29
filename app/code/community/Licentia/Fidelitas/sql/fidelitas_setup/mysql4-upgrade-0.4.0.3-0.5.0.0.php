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
-- Table structure for `fidelitas_stats`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_stats')}`;
CREATE TABLE `{$this->getTable('fidelitas_stats')}` (
  `stat_id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `subscriber_id` int(11) DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `view_at` datetime DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `clicks` int(11) DEFAULT NULL,
  `click_at` datetime DEFAULT NULL,
  PRIMARY KEY (`stat_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `customer_id` (`customer_id`),
  KEY `susbcriber_id` (`subscriber_id`),
  CONSTRAINT `FK_FIDELITAS_CAMPAIGN_STATS` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FIDELITAS_CUSTOMER_STATS` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FIDELITAS_SUSBCRIBER_STATS` FOREIGN KEY (`subscriber_id`) REFERENCES `{$this->getTable('fidelitas_subscribers')}` (`subscriber_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Log of views and clicks';
");


$installer->run("
-- ----------------------------
-- Table structure for `fidelitas_campaigns_links`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_campaigns_links')}`;
CREATE TABLE `{$this->getTable('fidelitas_campaigns_links')}` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `clicks` int(11) DEFAULT NULL,
  PRIMARY KEY (`link_id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `FK_FIDELITAS_CAMPAIGNS_LINKS` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Campaigns Links'");

$installer->run("
-- ----------------------------
-- Table structure for `fidelitas_urls`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_urls')}`;
CREATE TABLE `{$this->getTable('fidelitas_urls')}` (
  `url_id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `subscriber_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `visit_at` datetime DEFAULT NULL,
  `subscriber_firstname` varchar(255) DEFAULT NULL,
  `subscriber_lastname` varchar(255) DEFAULT NULL,
  `subscriber_email` varchar(255) DEFAULT NULL,
  `subscriber_cellphone` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`url_id`),
  KEY `link_id` (`link_id`),
  CONSTRAINT `FK_FIDELITAS_URL_LINK` FOREIGN KEY (`link_id`) REFERENCES `{$this->getTable('fidelitas_campaigns_links')}` (`link_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COMMENT='E-Goi - Campaigns Links Logs'

");

$installer->run("
-- ----------------------------
-- Table structure for `fidelitas_autoresponders`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_autoresponders')}`;
CREATE TABLE `{$this->getTable('fidelitas_autoresponders')}` (
  `autoresponder_id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `send_moment` enum('occurs','after') NOT NULL DEFAULT 'occurs',
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `after_days` smallint(2) DEFAULT NULL,
  `after_hours` smallint(1) DEFAULT NULL,
  `link_id` int(11) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `lists_ids` varchar(255) DEFAULT NULL,
  `from` varchar(255) NOT NULL,
  `number_subscribers` int(11) DEFAULT NULL,
  `channel` enum('email','sms') NOT NULL DEFAULT 'email',
  `send_once` enum('0','1') DEFAULT '1',
  `search` varchar(255) DEFAULT NULL,
  `search_option` enum('eq','like') DEFAULT 'eq',
  `order_status` varchar(255) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `segment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`autoresponder_id`,`from`),
  KEY `event` (`event`),
  KEY `campaign_id` (`campaign_id`),
  KEY `segment_id` (`segment_id`),
  CONSTRAINT `FK_FIDELITAS_AUTCAMPAIGNS` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FIDELITAS_AUTSEGMENS` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='E-Goi - List of Autoresponders'");


$installer->run("
-- ----------------------------
-- Table structure for `fidelitas_autoresponders_events`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_autoresponders_events')}`;
CREATE TABLE `{$this->getTable('fidelitas_autoresponders_events')}` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) DEFAULT NULL,
  `autoresponder_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `subscriber_id` int(11) DEFAULT NULL,
  `subscriber_firstname` varchar(255) DEFAULT NULL,
  `subscriber_lastname` varchar(255) DEFAULT NULL,
  `subscriber_email` varchar(255) DEFAULT NULL,
  `subscriber_cellphone` varchar(255) DEFAULT NULL,
  `send_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `sent` enum('0','1') NOT NULL DEFAULT '0',
  `channel` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `autoresponder_id` (`autoresponder_id`),
  KEY `subscriber_id` (`subscriber_id`),
  CONSTRAINT `FK_FIDELITAA_AUTSUBSCRIBER` FOREIGN KEY (`subscriber_id`) REFERENCES `{$this->getTable('fidelitas_subscribers')}` (`subscriber_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FIDELITAS_AUTEVENTS` FOREIGN KEY (`autoresponder_id`) REFERENCES `{$this->getTable('fidelitas_autoresponders')}` (`autoresponder_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of events in queue for autoresponder'");

  
$installer->run("DROP TABLE `{$this->getTable('fidelitas_logs')}`");


$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `clicks` int(11) NOT NULL DEFAULT 0 ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `views` int(11) NOT NULL DEFAULT 0 ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `unique_clicks` int(11) NOT NULL DEFAULT 0 ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `unique_views` int(11) NOT NULL DEFAULT 0 ");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `sent` int(11) NOT NULL DEFAULT 0");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `autoresponder_recipient` varchar(255)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `autoresponder` int(11)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `autoresponder_event` varchar(255) ");

$installer->endSetup();

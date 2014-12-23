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
-- Table structure for `fidelitas_campaigns_followup`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_campaigns_followup')}`;
CREATE TABLE `{$this->getTable('fidelitas_campaigns_followup')}` (
  `followup_id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `segment_id` int(11) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `active` enum('0','1') DEFAULT '0',
  `recipients` text,
  `recipients_options` varchar(255) DEFAULT NULL,
  `send_at` datetime DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `sent` enum('0','1') DEFAULT '0',
  PRIMARY KEY (`followup_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `segment_id` (`segment_id`),
  CONSTRAINT `FK_FIDELITAS_FOLLOW_CAMP` FOREIGN KEY (`campaign_id`) REFERENCES `{$this->getTable('fidelitas_campaigns')}` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FIDELITAS_FOLLOW_SEGMENT` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='E-Goi - Log of Campaigns Followups for CRON'
");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `followup_id` int(11)");

$installer->endSetup();
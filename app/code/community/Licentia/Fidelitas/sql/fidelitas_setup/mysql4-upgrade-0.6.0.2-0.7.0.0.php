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
-- Table structure for `fidelitas_campaigns_splits`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_campaigns_splits')}`;
    CREATE TABLE `{$this->getTable('fidelitas_campaigns_splits')}` (
   `split_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject_a` varchar(255) DEFAULT NULL,
  `subject_b` varchar(255) DEFAULT NULL,
  `sender_a` varchar(255) DEFAULT NULL,
  `sender_b` varchar(255) DEFAULT NULL,
  `message_a` text,
  `message_b` text,
  `views_a` int(11) NOT NULL DEFAULT '0',
  `views_b` int(11) NOT NULL DEFAULT '0',
  `clicks_a` int(11) NOT NULL DEFAULT '0',
  `clicks_b` int(11) NOT NULL DEFAULT '0',
  `conversions_a` int(11) NOT NULL DEFAULT '0',
  `conversions_b` int(11) NOT NULL DEFAULT '0',
  `days` smallint(3) DEFAULT NULL,
  `listnum` int(11) NOT NULL,
  `segment_id` int(11) DEFAULT NULL,
  `deploy_at` datetime DEFAULT NULL,
  `sent` enum('0','1') NOT NULL DEFAULT '0',
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `send_at` datetime DEFAULT NULL,
  `winner` varchar(255) DEFAULT NULL,
  `percentage` varchar(255) DEFAULT NULL,
  `last_subscriber_id` int(11) DEFAULT NULL,
  `closed` enum('0','1') NOT NULL DEFAULT '0',
  `recipients_a` text,
  `recipients_b` text,
  `recipients` text,
  PRIMARY KEY (`split_id`),
  KEY `segment_id` (`segment_id`),
  CONSTRAINT `FK_FIDELITAS_AB_SEGMENT` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='E-Goi - List of Campaigns A/B'
");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns_followup')}` DROP FOREIGN KEY `FK_FIDELITAS_FOLLOW_SEGMENT`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns_followup')}` ADD CONSTRAINT `FK_FIDELITAS_FOLLOW_SEGMENT` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`)   ON UPDATE SET NULL ON DELETE SET NULL");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_autoresponders')}` DROP FOREIGN KEY `FK_FIDELITAS_AUTSEGMENS`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_autoresponders')}` ADD CONSTRAINT `FK_FIDELITAS_AUTSEGMENS` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`)   ON UPDATE SET NULL ON DELETE SET NULL");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_groups')}` DROP FOREIGN KEY `FK_SEGMENT`");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_groups')}` ADD CONSTRAINT `FK_FIDELITAS_CONVSEGMENT` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('fidelitas_segments_subscribers')}` (`segment_id`)   ON UPDATE SET NULL ON DELETE SET NULL");

$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `split_id` int(11) DEFAULT NULL");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `split_version` varchar(255) DEFAULT NULL");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas_campaigns')}` ADD COLUMN `split_final` tinyint(4) DEFAULT NULL");


$installer->endSetup();
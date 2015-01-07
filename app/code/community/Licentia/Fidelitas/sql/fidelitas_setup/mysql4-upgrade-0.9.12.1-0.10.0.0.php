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
CREATE TABLE `{$this->getTable('fidelitas_abandoned')}` (
  `abandoned_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `is_active` enum('0','1') DEFAULT '1',
  `description` varchar(255) DEFAULT NULL,
  `channel` enum('sms','email') DEFAULT 'email',
  `groups` varchar(255) DEFAULT NULL,
  `stores` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `from` varchar(255) DEFAULT NULL,
  `message` text,
  `days` tinyint(4) DEFAULT NULL,
  `hours` tinyint(4) DEFAULT NULL,
  `minutes` tinyint(4) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `sent_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`abandoned_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='E-Goi - Abandoned Cart'");

$installer->run("
CREATE TABLE `{$this->getTable('fidelitas_abandoned_log')}` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `abandoned_id` int(11) DEFAULT NULL,
  `subscriber` varchar(11)  DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `abandoned_id` (`abandoned_id`),
  KEY `subscriber` (`subscriber`) USING BTREE,
  CONSTRAINT `FK_FIDELITAS_ABAND_LOGID` FOREIGN KEY (`abandoned_id`) REFERENCES `{$this->getTable('fidelitas_abandoned')}` (`abandoned_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Abandoned Cart Log'
");

$installer->endSetup();

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
-- Table structure for `fidelitas_lists_stores`
-- ----------------------------
DROP TABLE IF EXISTS `{$this->getTable('fidelitas_lists_stores')}`;
 CREATE TABLE `{$this->getTable('fidelitas_lists_stores')}` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `UDX_UNQ_ST_LT` (`store_id`),
  KEY `list_id` (`list_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `FK_FIDELITAS_LISTSTORES_LID` FOREIGN KEY (`list_id`) REFERENCES `{$this->getTable('fidelitas_lists')}` (`list_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FIDELITAS_LISTSTORES_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='E-Goi - Store Views for each List'
");

$installer->endSetup();
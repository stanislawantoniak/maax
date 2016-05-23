<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('zolago_dhl_zip')}` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
  `zip` VARCHAR(5) NOT NULL COMMENT 'ZIP',
  `country` VARCHAR(2) NOT NULL DEFAULT '' COMMENT 'Country',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_ZOLAGO_DHL_ZIP_ZIP` (`zip`)
) ENGINE=INNODB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COMMENT='ZIP'

");

$installer->endSetup();

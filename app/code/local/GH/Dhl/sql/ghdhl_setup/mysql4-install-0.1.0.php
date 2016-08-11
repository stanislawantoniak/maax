<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('ghdhl/dhl')};
CREATE TABLE IF NOT EXISTS {$this->getTable('ghdhl/dhl')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dhl_account` text(32) NOT NULL,
  `dhl_login` varchar(255) NOT NULL,
  `dhl_password` varchar(50) NOT NULL,
  `comment` text default NULL,
  `creation_time` datetime default NULL,
  `update_time` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='GH DHL Accounts' AUTO_INCREMENT=1 ;


");

$installer->endSetup();
<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("update `cms_block` set identifier = REPLACE(identifier, 'udropship-help', 'zos-help') where identifier like 'udropship-help%'");

$installer->endSetup();
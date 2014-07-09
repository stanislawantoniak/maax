<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("zolagorma/rma_reason_vendor");

$installer->getConnection()->addColumn($tableName, 'use_default', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment"	=> "Use default value flag"
));

$installer->endSetup();


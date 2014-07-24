<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("urma/rma");

$installer->getConnection()->addColumn($tableName, 'response_deadline', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_DATE,
	"comment"	=> "Response deadline"
));

$installer->endSetup();


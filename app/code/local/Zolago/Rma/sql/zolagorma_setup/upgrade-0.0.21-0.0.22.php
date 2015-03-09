<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("zolagorma/rma_reason");

$installer->getConnection()->addColumn($tableName, 'visible_on_front', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_BOOLEAN,
	"comment"	=> "Reason visible on front",
	"default" 	=> true,
	"nullable" 	=> false,
));
$tableName = $installer->getTable("urma/rma");
$installer->getConnection()->addColumn($tableName, 'rma_type', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
	"comment"	=> "Rma type",
	"default" 	=> 1,
	"nullable" 	=> false,
));

$installer->endSetup();


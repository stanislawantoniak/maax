<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

// Drop parent filter
$filterTable = $installer->getTable('zolagocatalog/category_filter');


// Add parent_attribute_id
$installer->getConnection()->addColumn(
		$filterTable, 
		"is_rolled", 
		array(
			"type" => Varien_Db_Ddl_Table::TYPE_SMALLINT,
			"nullable" => false,
			"default" => 0,			
			"comment" => "is rolled flag"
		));

$installer->endSetup();
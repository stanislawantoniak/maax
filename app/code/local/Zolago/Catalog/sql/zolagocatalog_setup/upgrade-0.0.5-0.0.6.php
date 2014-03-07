<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

// Drop parent filter
$filterTable = $installer->getTable('zolagocatalog/category_filter');

$installer->getConnection()->dropForeignKey(
		$filterTable, $installer->getFkName($filterTable, "parent_filter_id", $filterTable, "filter_id")
);

$installer->getConnection()->dropIndex(
		$filterTable, $installer->getIdxName($filterTable, array("parent_filter_id"))
);

$installer->getConnection()->dropColumn($filterTable, "parent_filter_id");

// Add parent_attribute_id
$installer->getConnection()->addColumn(
		$filterTable, 
		"parent_attribute_id", 
		array(
			"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
			"nullable" => true,
			"comment" => "Parent attribute id"
		));

$installer->getConnection()->addIndex(
		$filterTable, 
		$installer->getIdxName($filterTable, array("parent_attribute_id")), 
		array("parent_attribute_id")
);

$installer->getConnection()->addForeignKey(
		$installer->getFkName($filterTable, "parent_attribute_id", $this->getTable("eav/attribute"), "attribute_id"), 
		$filterTable, "parent_attribute_id", $this->getTable("eav/attribute"), "attribute_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);

$installer->endSetup();
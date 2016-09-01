<?php
/**
 * Add unique index for
 * card_number AND card_type
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable('zosloyaltycard/card');
$fields = array("card_number", "card_type");
$indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;
$indexName = $installer->getIdxName($tableName, $fields, $indexType);

$installer->getConnection()->modifyColumn($tableName, "card_number", array (
		'nullable'  => false,
		'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'    => 32,
		'comment'   => "Loyalty card number",
	)
);

$installer->getConnection()->modifyColumn($tableName, "card_type", array (
		'nullable'  => false,
		'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'    => 8,
		'comment'   => "Loyalty card type",
	)
);

$installer->getConnection()->addIndex(
	$tableName,
	$indexName,
	$fields,
	$indexType
);

$installer->endSetup();

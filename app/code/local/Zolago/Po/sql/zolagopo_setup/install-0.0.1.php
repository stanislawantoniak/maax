<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add zolago_defualt_pos_id field to PO
$poTabel = $this->getTable("udpo/po");
$posTabel = $this->getTable("zolagopos/pos");
$defultPosColumn = "default_pos_id";

$installer->getConnection()->addColumn($poTabel, $defultPosColumn, array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment" => $defultPosColumn
));

// Add index
$installer->getConnection()->addIndex(
		$poTabel, 
		$installer->getIdxName($poTabel, array($defultPosColumn)), 
		array($defultPosColumn)
);

// Add FK key
$installer->getConnection()->addForeignKey(
		$installer->getFkName($poTabel, $defultPosColumn, $posTabel, "pos_id"), 
		$poTabel, $defultPosColumn, $posTabel, "pos_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);

$installer->endSetup();

?>

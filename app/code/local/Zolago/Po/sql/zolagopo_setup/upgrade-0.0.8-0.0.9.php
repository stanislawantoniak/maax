<?php
/**
 * Add aggregated entity and relations
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$poTable = $this->getTable("udpo/po");
$aggregatedTable = $this->getTable("zolagopo/aggregated");

$table = $installer->getConnection()->newTable($aggregatedTable);

$table->addColumn("aggregated_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('aggregated_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), "Aggregated name")
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time');

$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn($poTable, "aggregated_id", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Aggregated Id",
	"nullable" => true
));

$installer->getConnection()->addIndex(
		$poTable, $installer->getIdxName($poTable, array("aggregated_id")), array("aggregated_id"));

$installer->getConnection()->addForeignKey(
		$installer->getFkName($poTable, "aggregated_id", $aggregatedTable, "aggregated_id"), 
		$poTable, "aggregated_id", $aggregatedTable, "aggregated_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
	);


$installer->endSetup();

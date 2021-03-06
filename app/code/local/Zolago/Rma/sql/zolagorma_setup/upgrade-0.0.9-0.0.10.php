<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("zolagorma/rma_reason_vendor");
$returnReasonTableName = $installer->getTable("zolagorma/rma_reason");
$vendorTableName = $installer->getTable("udropship/vendor");

if($installer->getConnection()->isTableExists($tableName) != true) {

    $table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn("vendor_return_reason_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))

    // Struct
    ->addColumn('vendor_id',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('return_reason_id',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('auto_days',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('allowed_days',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn("message", Varien_Db_Ddl_Table::TYPE_TEXT)

    // Misc
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')

    // Indexes
    ->addIndex($installer->getIdxName('zolagorma/rma_reason_vendor', array('vendor_return_reason_id')),
        array('vendor_return_reason_id'))
	->addIndex($installer->getIdxName('zolagorma/rma_reason_vendor', array('return_reason_id')),
        array('return_reason_id'))
    ->addIndex($installer->getIdxName('zolagorma/rma_reason_vendor', array('vendor_id')),
        array('vendor_id'))
    ->addIndex($installer->getIdxName('zolagorma/rma_reason_vendor', array('auto_days')),
        array('auto_days'))
    ->addIndex($installer->getIdxName('zolagorma/rma_reason_vendor', array('allowed_days')),
        array('allowed_days'))
	
	// Add FK keys
	->addForeignKey(
		$installer->getFkName($tableName, 'return_reason_id', $returnReasonTableName, "return_reason_id"), 
		'return_reason_id', $returnReasonTableName, "return_reason_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_CASCADE, Varien_Db_Adapter_Interface::FK_ACTION_NO_ACTION
	)
	
	->addForeignKey(
		$installer->getFkName($tableName, 'vendor_id', $vendorTableName, "vendor_id"), 
		'vendor_id', $vendorTableName, "vendor_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_CASCADE, Varien_Db_Adapter_Interface::FK_ACTION_NO_ACTION
	);
	
	$installer->getConnection()->createTable($table);
}

$installer->endSetup();


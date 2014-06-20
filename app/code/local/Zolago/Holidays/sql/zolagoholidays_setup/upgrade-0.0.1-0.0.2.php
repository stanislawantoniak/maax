<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable('zolagoholidays/processingtime');

if($installer->getConnection()->isTableExists($tableName) != true) {
	
	$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn("processingtime_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
        
    // Struct
	->addColumn('type',  Varien_Db_Ddl_Table::TYPE_TEXT, 11)
    ->addColumn("days", Varien_Db_Ddl_Table::TYPE_INTEGER, 11 )
    ->addColumn('hour',  Varien_Db_Ddl_Table::TYPE_TEXT, 50)
                    
    // Misc
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')
        
    // Indexes
    ->addIndex($installer->getIdxName('zolagoholidays/processingtime', array('processingtime_id')),
        array('processingtime_id'))
	->addIndex($installer->getIdxName('zolagoholidays/processingtime', array('type')),
        array('type'));
    
$installer->getConnection()->createTable($table);
	
	
}

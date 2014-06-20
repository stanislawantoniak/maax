<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable('zolagoholidays/holiday');

if($installer->getConnection()->isTableExists($tableName) != true) {
	
	$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn("holiday_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
        
    // Struct
    ->addColumn('country_id',  Varien_Db_Ddl_Table::TYPE_TEXT, 50)
    ->addColumn("name", Varien_Db_Ddl_Table::TYPE_TEXT, 100 )
	->addColumn('type',  Varien_Db_Ddl_Table::TYPE_TEXT, 50)
    ->addColumn("date", Varien_Db_Ddl_Table::TYPE_TEXT, 100 )
    ->addColumn('exclude_from_delivery', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array('default'=>1, 'nullable' => false))
    ->addColumn('exclude_from_pickup', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array('default'=>1, 'nullable' => false))
                    
    // Misc
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')
        
    // Indexes
    ->addIndex($installer->getIdxName('zolagoholidays/holiday', array('holiday_id')),
        array('holiday_id'))
    ->addIndex($installer->getIdxName('zolagoholidays/holiday', array('exclude_from_deliver')),
        array('exclude_from_delivery'))
	->addIndex($installer->getIdxName('zolagoholidays/holiday', array('exclude_from_pickup')),
        array('exclude_from_pickup'))
	->addIndex($installer->getIdxName('zolagoholidays/holiday', array('date')),
        array('date'));
    
$installer->getConnection()->createTable($table);
	
	
}

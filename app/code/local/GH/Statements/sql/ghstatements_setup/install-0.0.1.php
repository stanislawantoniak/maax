<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Basic structure of calendar
 */
 $table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/calendar'))
    ->addColumn("calendar_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    )) 
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Name') ;
$installer->getConnection()->createTable($table);
$table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/calendar_item'))
    ->addColumn("item_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    )) 
    ->addColumn("calendar_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    )) 
    ->addColumn('event_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Creation Time')
    ;                
$installer->getConnection()->createTable($table);

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "statements_calendar", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Statements calendar id",
	"nullable" => true
));

$installer->endSetup();

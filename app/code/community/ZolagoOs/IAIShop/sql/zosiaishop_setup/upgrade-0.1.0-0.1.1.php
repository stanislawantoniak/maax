<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

 $table = $installer->getConnection()
    ->newTable($installer->getTable('zosiaishop/log'))
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
	    'primary'   => true
    ),'IAI log ID')
	 ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		 'nullable'  => true,
		 'default'  => null
	 ),'Vendor ID')
	 ->addColumn("log", Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		 'nullable'  => false
	 ),'Log')
	 ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
		 array(
			 'nullable' => false,
			 'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
		 ), 'Created At')
	 ->addForeignKey(
		 $installer->getFkName('zosiaishop/log', 'vendor_id', 'udropship/vendor', 'vendor_id'),
		 'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
		 Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE);;

$installer->getConnection()->createTable($table);

$installer->endSetup();

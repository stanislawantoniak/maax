<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("zolagorma/returnreason");
if($installer->getConnection()->isTableExists($tableName) != true) {

    $table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn("return_reason_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))

    // Struct
    ->addColumn("name", Varien_Db_Ddl_Table::TYPE_TEXT, 100 )
    ->addColumn('auto_days',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('allowed_days',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn("message", Varien_Db_Ddl_Table::TYPE_TEXT)

    // Misc
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')

    // Indexes
    ->addIndex($installer->getIdxName('zolagorma/returnreason', array('return_reason_id')),
        array('return_reason_id'))
    ->addIndex($installer->getIdxName('zolagorma/returnreason', array('auto_days')),
        array('auto_days'))
    ->addIndex($installer->getIdxName('zolagorma/returnreason', array('allowed_days')),
        array('allowed_days'));

	$installer->getConnection()->createTable($table);

}

$installer->endSetup();

<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $this->getTable('ghstatements/vendor_balance');

$installer->getConnection()
    ->modifyColumn(
        $table,
        'date',
        array(
//            'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            /**
             *
             * Use TYPE_TEXT. TYPE_VARCHAR is deprecated.
             * @see Varien_Db_Adapter_Pdo_Mysql::$_ddlColumnTypes
             *
             */
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'comment' => 'Statement month (Miesiąc rozliczeniowy)',
            'length'    => '7'
        )
    );


$installer->getConnection()
    ->addIndex(
        $table, //$tableName
        $installer->getIdxName('ghstatements/vendor_balance', array('vendor_id', 'date')), //$indexName
        array('vendor_id', 'date'), //$fields
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE //$indexType
    );

$installer->endSetup();


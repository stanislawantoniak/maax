<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $this->getTable("reports/viewed_product_index");
$indexVisitorProduct = $installer->getIdxName('reports/viewed_product_index', array('visitor_id', 'product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

// Remove index
$installer->getConnection()->dropIndex($table, $indexVisitorProduct);

// Remove column visitor_id
$installer->getConnection()->dropColumn($table, 'visitor_id');

// Add column
$installer->getConnection()->addColumn(
    $table
    ,'sharing_code' // column name from wishlist table
    ,array(
         'type'   => Varien_Db_Ddl_Table::TYPE_TEXT // Deprecated Varien_Db_Ddl_Table::TYPE_VARCHAR @see lib/Varien/Db/Ddl/Table.php::304
        ,'length' => 32
        ,'nullable' => true
        ,'comment'  => 'sharing_code rom wishlist table'
    )
);

// Add index
$installer->getConnection()->addIndex(
     $table
    ,$installer->getIdxName($table, array('sharing_code', 'product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    ,array('sharing_code', 'product_id')
    ,Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
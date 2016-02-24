<?php
/**
 * @var $installer Mage_Catalog_Model_Resource_Setup
 */
$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');


$installer->startSetup();

$table = $installer->getTable('zolagocatalog/description_history');

$installer->getConnection()->addColumn(
    $table,
    "vendor_id",
    array(
        "type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
        "nullable" => false,
        "comment" => "Vendor Id"
    ));

$installer->getConnection()
    ->addIndex(
        $table,
        $installer->getIdxName('zolagocatalog/product_description_history', array('vendor_id')),
        array('vendor_id')
    );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('zolagocatalog/product_description_history', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        $table, 'vendor_id',
        $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->endSetup();
<?php
/**
 * Products stock in POS
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posStock = $installer->getTable("zolagopos/stock");

$installer->getConnection()->addIndex(
    $posStock,
    $installer->getIdxName(
        $posStock,
        array('product_id', 'pos_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('product_id', 'pos_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();

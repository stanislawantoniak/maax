<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$sizeTableRule = $installer->getTable("zolagosizetable/sizetable_rule");

/**
 * unikalny klucz na 3 pola
 */

$table = $installer->getConnection();

$table->addIndex(
    $sizeTableRule,
    $installer->getIdxName($sizeTableRule, array('brand_id', 'attribute_set_id', 'vendor_id'),Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
    array('brand_id', 'attribute_set_id', 'vendor_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );

$installer->endSetup();
<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$sizeTableScope = $installer->getTable("zolagosizetable/sizetable_scope");
$coreStoreTable = $installer->getTable('core_store');

/**
 * fk dla store_id
 */

$table = $installer->getConnection();

$table->addForeignKey(
    $installer->getFkName($sizeTableScope, 'store_id', $coreStoreTable, 'store_id'),
    $sizeTableScope,
    'store_id',
    $coreStoreTable,
    'store_id'
    );

$installer->endSetup();
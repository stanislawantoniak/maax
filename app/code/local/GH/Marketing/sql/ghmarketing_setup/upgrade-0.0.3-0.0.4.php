<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableMarketingCost = $this->getTable('ghmarketing/marketing_cost');

$installer->getConnection()
    ->dropForeignKey(
        $tableMarketingCost,
        $installer->getFkName('ghmarketing/marketing_cost', 'vendor_id', 'udropship/vendor', 'vendor_id')
    );

$installer->getConnection()
    ->dropForeignKey(
        $tableMarketingCost,
        $installer->getFkName('ghmarketing/marketing_cost', 'product_id', 'catalog/product', 'entity_id')
    );

$installer->getConnection()
    ->dropForeignKey(
        $tableMarketingCost,
        $installer->getFkName('ghmarketing/marketing_cost', 'type_id', 'ghmarketing/marketing_cost_type', 'marketing_cost_type_id')
    );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        $tableMarketingCost,
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'product_id', 'catalog/product', 'entity_id'),
        $tableMarketingCost,
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'type_id', 'ghmarketing/marketing_cost_type', 'marketing_cost_type_id'),
        $tableMarketingCost,
        'type_id', $installer->getTable('ghmarketing/marketing_cost_type'), 'marketing_cost_type_id',
        Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE);


$installer->endSetup();
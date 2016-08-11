<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableMarketingCost = $this->getTable('ghmarketing/marketing_cost');

$installer->getConnection()
    ->addColumn($tableMarketingCost, "billing_cost", array(
        "type" => Varien_Db_Ddl_Table::TYPE_FLOAT,
        "comment" => "Billing cost for vendor",
        "nullable" => false,
    ));

$installer->getConnection()->dropColumn($tableMarketingCost,"statement");

$installer->getConnection()
    ->addColumn($tableMarketingCost, "statement_id", array(
        "type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
        "comment" => "Statement id",
        "nullable" => true,
        "default" => null
    ));

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $tableMarketingCost, //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    );

$installer->getConnection()
    ->modifyColumn($this->getTable("udropship/vendor"),"cpc_commission", array(
        "type" => Varien_Db_Ddl_Table::TYPE_FLOAT,
        "comment" => "Vendor CPC commission rate",
    ));

$tableMarketingCostType = $this->getTable('ghmarketing/marketing_cost_type');

$installer->getConnection()->addIndex(
    $tableMarketingCostType,
    $installer->getIdxName(
        $tableMarketingCostType,
        'code',
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    'code',
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);


$installer->endSetup();
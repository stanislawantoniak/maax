<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('ghmarketing/marketing_budget'))
    ->addColumn("marketing_budget_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn("marketing_cost_type_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn("date", Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ))
    ->addColumn('budget', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        'nullable' => false,
    ))

    ->addIndex($installer->getIdxName('ghmarketing/marketing_budget', array('vendor_id')),
        array('vendor_id'))
    ->addIndex($installer->getIdxName('ghmarketing/marketing_budget', array('marketing_cost_type_id')),
        array('marketing_cost_type_id'))
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_budget', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_budget', 'marketing_cost_type_id', 'ghmarketing/marketing_cost_type', 'marketing_cost_type_id'),
        'marketing_cost_type_id', $installer->getTable('ghmarketing/marketing_cost_type'), 'marketing_cost_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);

$installer->endSetup();


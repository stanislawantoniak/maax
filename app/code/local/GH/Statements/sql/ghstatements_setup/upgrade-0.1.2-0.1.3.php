<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


/**
 * Structure of gh_statements_rma
 */
$installer->getConnection()
    ->addColumn(
        $installer->getTable('ghstatements/rma'),
        "approved_refund_amount",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            "length"	=> "12,4",
            'nullable'  => false,
            'comment'   => 'Approved refund amount'
        ));

$installer->getConnection()
    ->dropColumn(
        $installer->getTable('ghstatements/rma'),
        "shipping_cost"
    );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('urma/rma_item', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $installer->getTable('urma/rma_item'), //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->endSetup();

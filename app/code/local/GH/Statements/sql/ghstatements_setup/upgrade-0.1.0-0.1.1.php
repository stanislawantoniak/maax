<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


/**
 * Structure of gh_statements_rma
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/rma'))

    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id of statement rma')

    ->addColumn('statement_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Statement id')

    ->addColumn('po_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Udropship_po entity_id')

    ->addColumn('po_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => false,
    ), 'Udropship_po increment_id')

    ->addColumn('rma_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Urma_rma entity_id')

    ->addColumn('rma_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => false,
    ), 'Urma_rma increment_id')

    ->addColumn('event_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
    ), 'Event date')

    ->addColumn("sku", Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
    ),'SKU')

    ->addColumn("reason", Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false
    ),'Reason (title of urma item condition)')
    // $rmaItem->getItemConditionName() or
    // Mage::helper('urma')->getItemConditionTitle($rmaItem->getItemCondition())

    ->addColumn("payment_method", Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false
    ),'Payment Method')

    ->addColumn("payment_channel_owner", Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false
    ),'Payment Channel Owner')

    ->addColumn("price", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Price')
    ->addColumn("discount_amount", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Discount Amount')
    ->addColumn("final_price", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Final Price')
    ->addColumn("shipping_cost", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Shipping Cost')
    ->addColumn("commission_percent", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Commission Percent')
    ->addColumn("gallery_discount_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Gallery Discount Value')
    ->addColumn("commission_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Commission Value')
    ->addColumn("value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'Value');

$installer->getConnection()->createTable($table);

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghstatements/rma', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $installer->getTable('ghstatements/rma'), //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->endSetup();

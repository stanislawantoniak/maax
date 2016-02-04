<?php
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('sales_payment_transaction');
$installer->getConnection()->addColumn(
    $table,
    'dotpay_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => false,
        'length'    => 64,
        "comment"   => "Dotpay Customer ID"
    )
);

$installer->endSetup();
<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$salesAttributeSet = $installer->getTable('sales/payment_transaction');

$installer->getConnection()->addColumn(
    $salesAttributeSet,
    'bank_account',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment'  => 'Bank Account Number'
    )
);
$installer->getConnection()->addColumn(
    $salesAttributeSet,
    'rma_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'comment'  => 'RMA Id'
    )
);

$installer->endSetup();
<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$salesAttributeSet = $installer->getTable('sales/payment_transaction');


$installer->getConnection()->addColumn(
    $salesAttributeSet,
    'bank_transfer_create_at',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
        'nullable' => true,
        'default'  => NULL,
        'comment'  => 'Bank Transfer Create At'
    )
);

$installer->endSetup();
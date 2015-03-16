<?php

/**
 * zolago payment allocations table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('zolagopayment/allocation'),
        'refund_transaction_id',
        array(
            'nullable'  => true,
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment'   => "Refund transaction ID",
	        'default'   => null,
	        'unsigned'  => true
        )
    );

$installer->endSetup();
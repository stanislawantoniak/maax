<?php

/**
 * zolago payment allocations table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->dropForeignKey(
        $installer->getTable('zolagopayment/allocation'),
        $installer->getFkName('zolagopayment/allocation', 'customer_id', 'customer/entity', 'entity_id')
    );

$installer->getConnection()
    ->dropForeignKey(
        $installer->getTable('zolagopayment/allocation'),
        $installer->getFkName('zolagopayment/allocation', 'operator_id', 'zolagooperator/operator', 'operator_id')
    );


$installer->getConnection()
    ->addColumn(
        $installer->getTable('zolagopayment/allocation'),
        'vendor_id',
        array(
            'nullable'  => true,
            'unsigned'  => true,
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            "length"	=> 11,
            "comment"   => "udropship vendor id"
        )
    );

$installer->getConnection()
    ->addColumn(
        $installer->getTable('zolagopayment/allocation'),
        'is_automat',
        array(
            'nullable'  => false,
            'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            "comment"   => "if automat"
        )
    );



$installer->endSetup();
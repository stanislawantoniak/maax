<?php

/**
 * zolago payment allocations table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();


//customer_entity.entity_id
$installer->getConnection()
    ->addColumn($installer->getTable('zolagopayment/allocation'), "customer_id", array(
        "type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
        "length"	=> 10,
        'unsigned'  => true,
        'comment'   => 'Customer id'
    ));

    //FK
$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('zolagopayment/allocation', 'customer_id', 'customer/entity', 'entity_id'),
        $installer->getTable('zolagopayment/allocation'), 'customer_id',
        $installer->getTable('customer/entity'), 'entity_id'
    );


$installer->endSetup();


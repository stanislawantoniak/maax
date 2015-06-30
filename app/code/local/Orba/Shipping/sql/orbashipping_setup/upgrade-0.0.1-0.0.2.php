<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Change is_active to status
 */
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/shipment_track'), 
        "charge_total",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Total shipment charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/shipment_track'), 
        "charge_shipment",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Shipment charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/shipment_track'), 
        "charge_fuel",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Fuel charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/shipment_track'), 
        "charge_insurance",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Insurance charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/shipment_track'), 
        "charge_cod",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'COD charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('urma/rma_track'), 
        "charge_total",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Total shipment charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('urma/rma_track'), 
        "charge_shipment",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Shipment charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('urma/rma_track'), 
        "charge_fuel",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Fuel charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('urma/rma_track'), 
        "charge_insurance",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'Insurance charge'
        )
    );
 $installer->getConnection()
    ->addColumn(
        $installer->getTable('urma/rma_track'), 
        "charge_cod",
        array (
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'nullable' => true,
            'comment' => 'COD charge'
        )
    );
$installer->endSetup();

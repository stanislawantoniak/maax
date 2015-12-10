<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/track'),
        "shipping_source_account",
        array(
            "type"      => Varien_Db_Ddl_Table::TYPE_TEXT,
            "length"    => '64K',
            "comment"   => "DHL Shipping Source Account Number",
            "nullable"  => true
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/track'),
        "sales_track_id",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned'  => true,
            'nullable'  => true,
            'comment'   => 'Corresponding to sales_flat_shipment_track.entity_id',
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/track'),
        "rma_track_id",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned'  => true,
            'nullable'  => true,
            'comment'   => 'Corresponding to urma_rma_track.entity_id',
        )
    );

$installer->endSetup();


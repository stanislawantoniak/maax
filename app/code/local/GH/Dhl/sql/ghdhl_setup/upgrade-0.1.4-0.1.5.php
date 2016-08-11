<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer
    ->getConnection()
    ->addColumn($installer->getTable('sales/shipment_track'),
        'shipping_source_account',
         array(
            "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => '64K',
            "comment" => "DHL Shipping Source Account Number"
        ));


$rmaTrackTable = $installer->getTable("urma/rma_track");

$installer
    ->getConnection()
    ->addColumn($rmaTrackTable, 'shipping_source_account', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '64K',
        "comment" => "DHL Shipping Source Account Number"
    ));




$this->endSetup();

<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer
    ->getConnection()
    ->addColumn($installer->getTable('sales_flat_shipment_track'),
        'gallery_shipping_source',
        Varien_Db_Ddl_Table::TYPE_SMALLINT);


$rmaTrackTable = $installer->getTable("urma/rma_track");

$installer
    ->getConnection()
    ->addColumn($rmaTrackTable, 'gallery_shipping_source', array(
        "type" => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        "comment" => "Gallery Shipping Source",
        "length" => 1,
        "default" => 0
    ));

$this->endSetup();

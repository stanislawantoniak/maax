<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->changeColumn(
        $installer->getTable('sales_flat_shipment_track'),
        'gallery_shipping_source',
        'gallery_shipping_source',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            "comment" => "Gallery Shipping Source",
            "length" => 1,
            "default" => 0
        ));


$this->endSetup();

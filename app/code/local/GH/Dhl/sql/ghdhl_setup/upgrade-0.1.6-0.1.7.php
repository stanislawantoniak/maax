<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$sales_flat_shipment_track = $installer->getTable('sales_flat_shipment_track');
$installer->run("UPDATE  {$sales_flat_shipment_track}  SET gallery_shipping_source=0 WHERE entity_id IS NULL;");
$this->endSetup();

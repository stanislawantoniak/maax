<?php


$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('feedexport/feed')} ADD `product_status` int(1) DEFAULT NULL;
ALTER TABLE {$this->getTable('feedexport/feed')} ADD `product_visibility` int(1) DEFAULT NULL;
ALTER TABLE {$this->getTable('feedexport/feed')} ADD `product_type_id` text(20) DEFAULT NULL;
ALTER TABLE {$this->getTable('feedexport/feed')} ADD `product_inventory_is_in_stock` int(1) DEFAULT NULL;
");

$installer->endSetup();
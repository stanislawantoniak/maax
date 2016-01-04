<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$tableCatalogCategory = $resource->getTableName('catalog_category_product');
$tableCatalogProduct = $resource->getTableName('catalog_product_entity');
$query = "DELETE FROM {$tableCatalogCategory} where product_id NOT IN (SELECT entity_id FROM ({$tableCatalogProduct}))";
$writeConnection->query($query);

$installer->endSetup();

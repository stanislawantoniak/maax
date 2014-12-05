<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->updateAttribute (
                        'catalog_product', //Mage_Catalog_Model_Product::ENTITY,
                        Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_BRANDSHOP_CODE,
                        'is_global',
                        Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL);
$setup->updateAttribute (Mage_Catalog_Model_Product::ENTITY,
                        Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_BRANDSHOP_CODE,
                        'column_attribute_order',
                        25,25);
                        
$setup->updateAttribute (Mage_Catalog_Model_Product::ENTITY,
                        'udropship_vendor',
                        'column_attribute_order',
                        24,24);
//Adding Attribute converter_price_type
$installer->endSetup();

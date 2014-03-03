<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$catalogInstaller = Mage::getResourceModel("catalog/setup", "core_setup");
/* @var $catalogInstaller Mage_Catalog_Model_Resource_Setup */

$catalogInstaller->addAttribute(
        Mage_Catalog_Model_Product::ENTITY, 
             "wishlist_count", 
             array(
               "type"              => "int",
               "input"             => "text",
               "required"          => 0,
               "frontend_class"    => "validate-digits",
               "filterable"        => 1,
               "comparable"        => 1,
               //"visible_on_front"  => 1
               "used_in_product_listing" => 1,
               "used_for_sort_by" => 1,
               "label"             => "Wishlist item count",
               "default"           => "0",
               "position"          => 900,
               "group"             => "General"
           )
    );

$installer->endSetup();

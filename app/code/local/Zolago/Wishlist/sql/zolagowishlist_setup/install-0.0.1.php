<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Modify orig table

$wishlistTable = $installer->getTable("wishlist/wishlist");
$fkName = $installer->getFkName('wishlist/wishlist', 'customer_id', 'customer/entity', 'entity_id');

// Drop key
$installer->getConnection()->dropForeignKey($wishlistTable, $fkName);

// Add null
$installer->getConnection()->modifyColumn($wishlistTable, "customer_id", array(        
        'unsigned'  => true,
        'nullable'  => true,
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'default'   => '0')
);

// Add key again
$installer->getConnection()->addForeignKey($fkName, $wishlistTable, "customer_id", 
        $installer->getTable('customer/entity'), 'entity_id', 
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
);

// Add attribute wishlist_count

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

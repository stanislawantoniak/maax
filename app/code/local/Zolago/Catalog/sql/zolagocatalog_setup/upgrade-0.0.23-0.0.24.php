<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();
$installer->removeAttribute('catalog_product', 'product_flag');

//New Flag Attributes
$installer->addAttribute('catalog_product', 'product_flag', array(
        'group' => 'General',
        'input' => 'select',
        'type' => 'int',
        'label' => 'Product Flags',
        'source' => 'zolagocatalog/product_source_flag',
        'visible' => true,
        'required' => false,
        'visible_on_front' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'user_defined' => true,
        'default' => '',
        'used_in_product_listing' => 1,
        'used_for_promo_rules' => 1,
        'searchable' => true,
        'filterable' => true,
        'filterable_in_search' => true,
        'comparable' => false,
        'visible_on_front' => true
    )
);

$installer->endSetup();





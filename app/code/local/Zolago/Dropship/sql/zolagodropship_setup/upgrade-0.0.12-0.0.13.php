<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */


$installer->startSetup();

$code = 'index_product_by_google';

//Index By Google Attribute
$installer->addAttribute('catalog_product', $code, array(
        'group' => 'General',
        'input' => 'select',
        'type' => 'int',
        'label' => Mage::helper('zolagodropship')->__("Index By Google"),
        'source' => 'zolagodropship/source_indexbygoogle',
        'visible' => true,
        'required' => false,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined' => true,
        'default' => '',
        'used_in_product_listing' => false,
        'used_for_promo_rules' => false,
        'searchable' => false,
        'filterable' => false,
        'filterable_in_search' => false,
        'comparable' => false,
        'visible_on_front' => false
    )
);

$installer->endSetup();





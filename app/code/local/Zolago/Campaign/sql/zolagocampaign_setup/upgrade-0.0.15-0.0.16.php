<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
$installer->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE,
    array(
        "is_searchable" => 1,
        "is_visible_in_advanced_search" => 1,
        'used_in_product_listing' => 1,
        'is_used_for_promo_rules' => 1,
    )
);
$installer->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE,
    array(
        "is_searchable" => 1,
        "is_visible_in_advanced_search" => 1,
        'used_in_product_listing' => 1,
        'is_used_for_promo_rules' => 1,
    )
);

$installer->endSetup();





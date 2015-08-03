<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
$installer->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE,
    array(
        "is_filterable_in_search" => 1
    )
);
$installer->updateAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE,
    array(
        "is_filterable_in_search" => 1
    )
);

$installer->endSetup();





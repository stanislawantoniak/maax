<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');


$installer->startSetup();

$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE);

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE,
    array(
        'label' => 'Regular Campaign',
        'group' => 'Campaign',
        'input' => 'select',
        'source' => 'zolagocampaign/attribute_source_campaign', //Zolago_Campaign_Model_Attribute_Source_Campaign
        'input_renderer' => 'zolagocampaign/helper_form_regularcampaign',
        'type' => 'int',
        'filterable' => 1,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'user_defined' => true,
        'position' => 1
    )
);


$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE);

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE,
    array(
        'label' => 'Info Campaign',
        'group' => 'Campaign',
        'input' => 'multiselect',
        'source' => 'zolagocampaign/attribute_source_campaign_info', //Zolago_Campaign_Model_Attribute_Source_Campaign_Info
        'input_renderer' => 'zolagocampaign/helper_form_infocampaign',
        'type' => 'varchar',
        'filterable' => 1,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'user_defined' => true,
        'position' => 2
    )
);

$installer->endSetup();





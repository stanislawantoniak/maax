<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');


$installer->startSetup();

$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_STRIKEOUT_PRICE_TYPE_CODE);

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_STRIKEOUT_PRICE_TYPE_CODE,
    array(
        'label' => 'Strikeout price type',
        'group' => 'Campaign',
        'input' => 'select',
        'source' => 'zolagocampaign/campaign_strikeout',//Zolago_Campaign_Model_Campaign_Strikeout
        'input_renderer' => 'zolagocampaign/helper_form_strikeoutprice',
        'type' => 'int',
        'filterable' => 1,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'user_defined' => true
    )
);

$installer->endSetup();





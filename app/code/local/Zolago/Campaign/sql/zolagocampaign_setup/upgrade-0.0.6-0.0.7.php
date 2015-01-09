<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_STRIKEOUT_PRICE_TYPE_CODE,
    array(
        'label' => 'Strikeout price type',
        'group' => 'Campaign',
        'input' => 'select',
        'source' => 'zolagocampaign/attribute_source_campaign',
        'input_renderer' => 'zolagocampaign/helper_form_regularcampaign',
        'frontend' => '',
        'backend' => '',
        'type' => 'int',
        'backend_type' => 'static',
        'is_filterable' => 1,
        'used_in_product_listing' => 0,
        'is_used_for_promo_rules' => 0,
        'used_for_sort_by' => 0,
        'visible' => true,
        'required' => false,
        'filterable' => true,
        'filterable_in_search' => false,
        'is_filterable_in_search' => false,
        'searchable' => false,
        'visible_on_front' => false,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'user_defined' => true,
        'position' => 1,
    )
);

$installer->endSetup();





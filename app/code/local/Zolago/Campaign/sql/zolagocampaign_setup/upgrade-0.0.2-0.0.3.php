<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE,
    array(
         'label'                   => 'Regular Campaign',
         'group'                   => 'Campaign',
         'input'                   => 'select',
         'source'                  => 'zolagocampaign/attribute_source_campaign',
         'frontend'                => '',
         'backend'                 => '',
         'type'                    => 'int',
         'backend_type'            => 'static',
         'is_filterable'           => 1,
         'used_in_product_listing' => 0,
         'is_used_for_promo_rules' => 0,
         'used_for_sort_by'        => 0,
         'visible'                 => true,
         'required'                => false,
         'filterable'              => true,
         'filterable_in_search'    => false,
         'is_filterable_in_search' => false,
         'searchable'              => false,
         'visible_on_front'        => false,
         'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
         'user_defined'            => true,
         'position'                => 1,

    )
);
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE,
    array(
         'label'                   => 'Info Campaign',
         'group'                   => 'Campaign',
         'type'                    => 'varchar',
         'input'                   => 'multiselect',
         'backend'                 => 'eav/entity_attribute_backend_array',
         'frontend'                => '',
         'source'                  => 'zolagocampaign/attribute_source_campaign_info',
         'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
         'visible'                 => true,
         'is_filterable'           => 1,
         'used_in_product_listing' => 0,
         'is_used_for_promo_rules' => 0,
         'used_for_sort_by'        => 0,
         'required'                => false,
         'user_defined'            => true,
         'searchable'              => false,
         'backend_type'            => 'static',
         'filterable'              => true,
         'filterable_in_search'    => false,
         'is_filterable_in_search' => false,
         'comparable'              => false,
         'visible_on_front'        => false,
         'unique'                  => 'false',

    )
);

$installer->endSetup();





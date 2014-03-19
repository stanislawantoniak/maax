<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();


// new attributes (flags, reviews)
$installer->addAttribute('catalog_product','product_flag', array (
                             'label' => 'Product Flags',
                             'group' => 'General',
                             'type' => 'varchar',
                             'input' => 'multiselect',
                             'backend' => 'eav/entity_attribute_backend_array',
                             
                             'frontend' => '',
                             'source' => 'zolagocatalog/product_source_flag',
                             'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                             'visible' => true,
                             'is_filterable' => 1,
                             'used_in_product_listing' => 1,
                             'is_used_for_promo_rules' => 1,
                             'used_for_sort_by' => 1,
                             'required' => false,
                             'user_defined'              => true,       
                             'searchable' => true,
                             'backend_type' => 'static',
                             'filterable' => true,
                             'filterable_in_search' => true,
                             'is_filterable_in_search' => true,
                             'comparable' => false,                                                     
                             'visible_on_front' => true,
                             'unique' => 'false',

                         ));
$installer->addAttribute('catalog_product','product_rating', array (
                             'label' => 'Product Rating',
                             'group' => 'General',
                             'input' => 'select',
                             'source' => 'zolagocatalog/product_source_rating',
                             'frontend' => '',
                             'backend' => '',
                             'type' => 'int',
                             'backend_type' => 'static',
                             'is_filterable' => 1,
                             'used_in_product_listing' => 1,
                             'is_used_for_promo_rules' => 1,
                             'used_for_sort_by' => 1,
                             'visible' => true,
                             'required' => false,
                             'filterable' => true,
                             'filterable_in_search' => true,
                             'is_filterable_in_search' => true,
                             'searchable' => true,
                             'visible_on_front' => true,
                             'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                             'user_defined'              => true,
                             'default'                   => '0',
                             'position'                  => 120,

                         ));

// Drop parent filter
$installer->endSetup();





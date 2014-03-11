<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/*
 * Add Category Attributes related to Filters
 */
$installer->addAttribute('catalog_category', 'use_price_filter', array(
    'group'                     => 'General Information',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Use Price Filter',
	'source'            		=> 'eav/entity_attribute_source_boolean',
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 100
));

$installer->addAttribute('catalog_category', 'use_review_filter', array(
    'group'                     => 'General Information',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Use Review Filter',
	'source'            		=> 'eav/entity_attribute_source_boolean',
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 110
));

$installer->addAttribute('catalog_category', 'use_flag_filter', array(
    'group'                     => 'General Information',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Use Flag Filter',
	'source'            		=> 'eav/entity_attribute_source_boolean',
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 120
));

$installer->endSetup();
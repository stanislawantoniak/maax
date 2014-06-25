<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();
$installer->addAttribute('catalog_product', 'gallery_to_check', array(
		'group'                     => 'General',
		'input'                     => 'select',
		'type'                      => 'int',
		'label'                     => 'Image gallery to check',
		'source'            		=> 'eav/entity_attribute_source_boolean',
		'backend'                   => '',
		'visible'                   => true,
		'required'                  => true,
		'visible_on_front'          => false,
		'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'user_defined'      		=> false,
		'default'           		=> '1',
		'used_in_product_listing'	=> 0,
		'used_for_promo_rules'		=> 0,
		'searchable'				=> false,
		'filterable'				=> false,
		'filterable_in_search'		=> false,
		'comparable'				=> false,
	)
);

$installer->endSetup();





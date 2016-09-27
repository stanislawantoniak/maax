<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$installer->addAttribute('catalog_product', 'is_founding', array(
		'group'                     => 'General',
		'input'                     => 'select',
		'type'                      => 'int',
		'label'                     => 'Can have founding',
		'source'            		=> 'eav/entity_attribute_source_boolean',
		'backend'                   => '',
		'visible'                   => true,
		'required'                  => false,
		'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'user_defined'      		=> true,
		'default'           		=> '',
		'used_in_product_listing'	=> 0,
		'used_for_promo_rules'		=> 0,
		'searchable'				=> false,
		'filterable'				=> false,
		'filterable_in_search'		=> false,
		'comparable'				=> false,
		'visible_on_front'			=> false
	)
);


$installer->addAttribute('catalog_product', 'is_installments', array(
		'group'                     => 'General',
		'input'                     => 'select',
		'type'                      => 'int',
		'label'                     => 'Can be buy in installments',
		'source'            		=> 'eav/entity_attribute_source_boolean',
		'backend'                   => '',
		'visible'                   => true,
		'required'                  => false,
		'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'user_defined'      		=> true,
		'default'           		=> '',
		'used_in_product_listing'	=> 0,
		'used_for_promo_rules'		=> 0,
		'searchable'				=> false,
		'filterable'				=> false,
		'filterable_in_search'		=> false,
		'comparable'				=> false,
		'visible_on_front'			=> false
	)
);

$installer->endSetup();




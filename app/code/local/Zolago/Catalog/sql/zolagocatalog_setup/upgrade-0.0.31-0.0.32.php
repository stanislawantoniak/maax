<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/*
 * Add Category Attributes related category
 */
$installer->addAttribute('catalog_category', 'related_category', array(
    'group'                     => 'General Information',
    'input'                     => 'text',
    'type'                      => 'int',
    'label'                     => 'Related category Id',
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 110
));
/*
 * Add Category Attributes canonical link
 */
$installer->addAttribute('catalog_category', 'canonical_link', array(
    'group'                     => 'General Information',
    'input'                     => 'text',
    'type'                      => 'text',
    'label'                     => 'Canonical link',
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 120
));
/*
 * Add Category Attributes related category
 */
$installer->addAttribute('catalog_category', 'related_category_products', array(
    'group'                     => 'General Information',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Add products from related category',
    'backend'                   => '',
    'source'					=> 'eav/entity_attribute_source_boolean',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> false,
    'position'            		=> 130
));

$installer->endSetup();
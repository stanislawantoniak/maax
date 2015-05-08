<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/*
 * Add Category Attributes basic category
 */
$installer->addAttribute('catalog_category', 'basic_category', array(
    'group'                     => 'General Information',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Basic Category',
    'source'            		=> 'eav/entity_attribute_source_boolean',
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 100
));

$installer->endSetup();
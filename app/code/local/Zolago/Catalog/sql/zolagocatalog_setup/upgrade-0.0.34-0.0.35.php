<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/*
 * Add Category Attributes related to Filters
 */
$installer->addAttribute('catalog_product', 'description_accepted', array(
    'group'                     => 'General',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Description accepted',
	'source'            		=> 'eav/entity_attribute_source_boolean',
    'backend'                   => '',
    'visible'                   => false,
    'required'                  => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 1005
));

<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/*
 * Add Category Attributes basic category
 */
$installer->addAttribute('catalog_category', 'dynamic_meta_title', array(
    'group'                     => 'Dynamic Meta Information',
    'input'                     => 'text',
    'type'                      => 'varchar',
    'label'                     => 'Meta Title',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 1
));
$installer->addAttribute('catalog_category', 'dynamic_meta_keywords', array(
    'group'                     => 'Dynamic Meta Information',
    'input'                     => 'textarea',
    'type'                      => 'text',
    'label'                     => 'Meta Keywords',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 2
));
$installer->addAttribute('catalog_category', 'dynamic_meta_description', array(
    'group'                     => 'Dynamic Meta Information',
    'input'                     => 'textarea',
    'type'                      => 'text',
    'label'                     => 'Meta Description',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> '',
    'position'            		=> 3
));

$installer->endSetup();
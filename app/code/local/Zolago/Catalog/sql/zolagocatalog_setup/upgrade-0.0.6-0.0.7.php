<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('catalog_category', 'price_filter_settings', array(
    'group'                     => 'Display Settings',
    'input'                     => 'text',
    'type'                      => 'varchar',
    'label'                     => 'Price Filter Settings',
	'source'            		=> null,
    'backend'                   => '',
    'visible'                   => true,
    'required'                  => false,
    'visible_on_front'          => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'user_defined'      		=> true,
    'default'           		=> '100;300',
    'position'            		=> 100
));

// Drop parent filter
$installer->endSetup();
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

// new attributes (flags, reviews)
$installer->addAttribute('catalog_product','product_flag', array (
                             'label' => 'Product Flags',
                             'type' => 'varchar',
                             'group' => 'General',
                             'input' => 'multiselect',
                             'backend' => 'eav/entity_attribute_backend_array',
                             'frontend' => '',
                             'source' => '',
                             'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                             'visible' => true,
                             'required' => false,
                             'user_defined'              => true,
                             'searchable' => false,
                             'filterable' => false,
                             'comparable' => false,                                                     
                             'visible_on_front' => true,
                             'unique' => 'false',
                             'option' => array (
                                 'value' => array (
                                      '1' => array('New'),
                                      '2' => array('Sale'),
                                      '3' => array('Promotion'),
                                      '4' => array('Bestseller'),
                                  ),
                               )                             

                         ));
$installer->addAttribute('catalog_product','product_rating', array (
                             'group' => 'General',
                             'input' => 'select',
                             'source' => '',
                             'frontend' => '',
                             'backend' => 'eav/entity_attribute_backend_array',
                             'type' => 'decimal',
                             'label' => 'Product Rating',
                             'visible' => true,
                             'required' => false,
                             'visible_on_front' => true,
                             'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                             'user_defined'              => true,
                             'default'                   => '0',
                             'position'                  => 120,
                             'option' => array (
                                 'value' => array (
                                      '0' => array('no rating'),
                                      '1' => array('1'),
                                      '2' => array('2'),
                                      '3' => array('3'),
                                      '4' => array('4'),
                                      '5' => array('5'),
                                  ),
                               )                             

                         ));

// Drop parent filter
$installer->endSetup();





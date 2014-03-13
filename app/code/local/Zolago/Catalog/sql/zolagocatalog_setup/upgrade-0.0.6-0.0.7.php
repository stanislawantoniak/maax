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
                             'group' => 'General',
                             'input' => 'multiselect',
                             'source' => 'zolagocatalog/product_source_flag',
                             'backend' => 'eav/entity_attribute_backend_array',
                             'type' => 'varchar',
                             'label' => 'Product Flags',
                             'visible' => true,
                             'required' => false,
                             'visible_on_front' => true,
                             'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                             'user_defined'              => true,
                             'default'                   => '',
                             'position'                  => 110,
                             'option' => array (
                                 'value' => array (
                                      '1' => 'New',
                                      '2' => 'Sale',
                                      '3' => 'Promotion',
                                      '4' => 'Bestseller',
                                  ),
                               )
                             

                         ));
$installer->addAttribute('catalog_product','product_rating', array (
                             'group' => 'General',
                             'input' => 'select',
                             'source' => 'zolagocatalog/product_source_rating',
                             'type' => 'decimal',
                             'label' => 'Product Rating',
                             'visible' => true,
                             'required' => false,
                             'visible_on_front' => true,
                             'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                             'user_defined'              => true,
                             'default'                   => '0',
                             'position'                  => 120

                         ));

// Drop parent filter
$installer->endSetup();




$installer = $this;

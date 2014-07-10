<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/*
 * Remove attribute from General Information
 */

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->removeAttribute('catalog_category', Zolago_Solrsearch_Helper_Data::ZOLAGO_USE_IN_SEARCH_CONTEXT);

/*
 * Add Category Attributes related to Filters
 */
$setup->addAttribute(
    'catalog_category', Zolago_Solrsearch_Helper_Data::ZOLAGO_USE_IN_SEARCH_CONTEXT,
    array(
         'group'            => 'Display Settings',
         'input'            => 'select',
         'type'             => 'int',
         'label'            => 'Use in search context',
         'source'           => 'eav/entity_attribute_source_boolean',
         'backend'          => '',
         'visible'          => true,
         'required'         => false,
         'visible_on_front' => true,
         'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
         'user_defined'     => true,
         'default'          => 0,
         'position'         => 120
    )
);

$installer->endSetup();
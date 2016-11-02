<?php
$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Zolago_Catalog_Model_Resource_Setup */

$installer->startSetup();

//Adding Attribute converter_msrp_type
$attributePriceType = $installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
	Zolago_Sizetable_Model_Sizetable::ZOLAGO_SIZETABLE_ATTRIBUTE_CODE,
    array(
         'group'      => 'General',
         'type'       => 'int',
         'input'      => 'select',
         'label'      => 'Custom sizetable',
         'required'   => false,
         'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
         'source'    => 'zolagosizetable/source_attribute',
    )
);

$installer->endSetup();

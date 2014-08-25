<?php

$installer = $this;
/* @var $installer Zolago_Catalog_Model_Resource_Setup */

$installer->startSetup();

//Adding Attribute converter_msrp_type
$attributePriceType = $installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, 
	Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE,
    array(
         'group'      => 'Prices',
         'type'       => 'int',
         'input'      => 'select',
         'label'      => 'Converter Msrp Type',
         'sort_order' => 21,
		 'default'    => Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_AUTO,
         'required'   => false,
         'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
         'source'    => 'zolagocatalog/product_source_convertermsrptype'
    )
);

$installer->endSetup();
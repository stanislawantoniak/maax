<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

//Adding Attribute converter_price_type
$attributePriceTypeCode = Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE;
$attributePriceType = $setup->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, $attributePriceTypeCode,
    array(
         'group'      => 'Prices',
         'type'       => 'int',
         'input'      => 'select',
         'label'      => 'Converter Price Type',
         'sort_order' => 20,
         'set_id'     => 4,
         'required'   => false,
         'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
         'backend'    => 'eav/entity_attribute_backend_array',
         'option'     => array(
             'values' => array(
                 0 => 'A',
                 1 => 'B',
                 2 => 'C',
                 3 => 'Z',
             )
         ),

    )
);
//Adding Attribute price_margin
$attributePriceMarginCode = Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE;
$attributePriceMargin = $setup->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, $attributePriceMarginCode,
    array(
         'group'      => 'Prices',
         'type'       => 'text',
         'input'      => 'text',
         'label'      => 'Price margin, %',
         'sort_order' => 21,
         'set_id'     => 4,
         'required'   => false,
         'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
         'backend'    => ''
    )
);

$installer->endSetup();
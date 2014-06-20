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
         'group'      => 'Price',
         'type'       => 'int',
         'input'      => 'select',
         'label'      => 'Converter Price Type',
         'sort_order' => 1,
         'set_id'     => 4,
         'required'   => false,
         'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
         'backend'    => 'eav/entity_attribute_backend_array',
         'option'     => array(
             'values' => array(
                 0 => 'A',
                 1 => 'B',
                 2 => 'D',
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
         'group'      => 'Price',
         'type'       => 'text',
         'input'      => 'text',
         'label'      => 'Price margin, %',
         'sort_order' => 2,
         'set_id'     => 4,
         'required'   => false,
         'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
         'backend'    => ''
    )
);

$entityTypeId = Mage::getModel('eav/entity')
    ->setType('catalog_product')
    ->getTypeId();

$attributePriceTypeId = $attributePriceType->getAttributeId($entityTypeId, $attributePriceTypeCode);
$attributePriceMarginId = $attributePriceMargin->getAttributeId($entityTypeId, $attributePriceMarginCode);

$collection = Mage::getResourceModel('eav/entity_attribute_set_collection');
$collection
    ->getSelect()
    ->reset('columns')
    ->join(
        array('attribute_group' => 'eav_attribute_group'),
        'attribute_group.attribute_set_id=main_table.attribute_set_id',
        array(
             'main_table.attribute_set_id AS set',
             'attribute_group_id AS group'
        )
    )
    ->where("attribute_group_name='Prices'");
$attributeSetsGroups = $collection->getData();

if (!empty($attributeSetsGroups)) {
    foreach ($attributeSetsGroups as $attributeSetsGroupsItem) {
        $setup->addAttributeToSet(
            Mage_Catalog_Model_Product::ENTITY,
            (int)$attributeSetsGroupsItem['set'],
            (int)$attributeSetsGroupsItem['group'],
            $attributePriceTypeId
        );
        $setup->addAttributeToSet(
            Mage_Catalog_Model_Product::ENTITY,
            (int)$attributeSetsGroupsItem['set'],
            (int)$attributeSetsGroupsItem['group'],
            $attributePriceMarginId
        );
    }
}

$installer->endSetup();
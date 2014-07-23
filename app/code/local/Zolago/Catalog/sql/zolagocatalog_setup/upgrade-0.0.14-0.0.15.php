<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

//Adding Attribute price_margin
$entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
$attributePriceMarginCode = Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE;
$attribute = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeID, $attributePriceMarginCode);
$data = array('frontend_class' => 'validate-number');
$attribute->addData($data);

try {
    $attribute->save();
} catch (Exception $e) {
    Mage::exception($e);
}

$installer->endSetup();
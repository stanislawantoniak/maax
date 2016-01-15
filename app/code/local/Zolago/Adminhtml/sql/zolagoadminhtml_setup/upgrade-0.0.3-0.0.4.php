<?php
/**
 *
 */
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('customer');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

/* 1. Add attributes to customer entity */
$installer->addAttribute("customer", "loyalty_card_number_1", array(
    "type" => "varchar",
    "backend" => "",
    "label" => "1. Loyalty card number",
    "input" => "text",
    "source" => "",
    "visible" => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique" => false

));
$installer->addAttribute("customer", "loyalty_card_number_2", array(
    "type" => "varchar",
    "backend" => "",
    "label" => "2. Loyalty card number",
    "input" => "text",
    "source" => "",
    "visible" => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique" => false

));
$installer->addAttribute("customer", "loyalty_card_number_3", array(
    "type" => "varchar",
    "backend" => "",
    "label" => "3. Loyalty card number",
    "input" => "text",
    "source" => "",
    "visible" => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique" => false

));

/* 2. Add attributes to group */
$attribute1 = Mage::getSingleton("eav/config")->getAttribute("customer", "loyalty_card_number_1");
$attribute2 = Mage::getSingleton("eav/config")->getAttribute("customer", "loyalty_card_number_2");
$attribute3 = Mage::getSingleton("eav/config")->getAttribute("customer", "loyalty_card_number_3");


$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'loyalty_card_number_1',
    '999'  //sort_order
);
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'loyalty_card_number_2',
    '998'  //sort_order
);
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'loyalty_card_number_3',
    '997'  //sort_order
);


/* 3. Set attributes as used_in_forms */
$usedInForms = array("adminhtml_gh_offline");


$attribute1->setData("used_in_forms", $usedInForms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100);
$attribute1->save();

$attribute2->setData("used_in_forms", $usedInForms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100);
$attribute2->save();

$attribute3->setData("used_in_forms", $usedInForms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100);
$attribute3->save();


$installer->endSetup();
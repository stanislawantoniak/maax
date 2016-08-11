<?php
/**
 *
 */
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('customer');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

/** @var Mage_Eav_Model_Config $eavConfig */
$eavConfig = Mage::getSingleton("eav/config");
/* attributes as used_in_forms */
$usedInForms = array("adminhtml_gh_offline");

$attribute1 = $eavConfig->getAttribute("customer", "loyalty_card_number_1");
$attribute1->setData("sort_order", 100);
$attribute1->save();

$attribute1 = $eavConfig->getAttribute("customer", "loyalty_card_number_2");
$attribute1->setData("sort_order", 200);
$attribute1->save();

$attribute1 = $eavConfig->getAttribute("customer", "loyalty_card_number_3");
$attribute1->setData("sort_order", 300);
$attribute1->save();

$installer->removeAttribute($entityTypeId, 'loyalty_card_number_1_type');
$installer->removeAttribute($entityTypeId, 'loyalty_card_number_2_type');
$installer->removeAttribute($entityTypeId, 'loyalty_card_number_3_type');
$installer->removeAttribute($entityTypeId, 'loyalty_card_number_1_expire');
$installer->removeAttribute($entityTypeId, 'loyalty_card_number_2_expire');
$installer->removeAttribute($entityTypeId, 'loyalty_card_number_3_expire');

/* 1. Add attributes to customer entity */
$installer->addAttribute("customer", "loyalty_card_number_1_type", array(
	"type" => "int",
	"backend" => "",
	"label" => "1. Loyalty card number type",
	"input" => "select",
	"source" => "zolagocustomer/source_loyalty_card_types",
	"visible" => true,
	"required" => false,
	"default" => "",
	"frontend" => "",
	"unique" => false,
	"sort_order" => 110
));

$installer->addAttribute("customer", "loyalty_card_number_1_expire", array(
	"type" => "datetime",
	"backend" => "",
	"label" => "1. Loyalty card number expire at",
	"input" => "date",
	"visible" => true,
	"required" => false,
	"default" => "",
	"frontend" => "",
	"unique" => false,
	"sort_order" => 120
));

$installer->addAttribute("customer", "loyalty_card_number_2_type", array(
	"type" => "int",
	"backend" => "",
	"label" => "2. Loyalty card number type",
	"input" => "select",
	"source" => "zolagocustomer/source_loyalty_card_types",
	"visible" => true,
	"required" => false,
	"default" => "",
	"frontend" => "",
	"unique" => false,
	"sort_order" => 210
));

$installer->addAttribute("customer", "loyalty_card_number_2_expire", array(
	"type" => "datetime",
	"backend" => "",
	"label" => "2. Loyalty card number expire at",
	"input" => "date",
	"visible" => true,
	"required" => false,
	"default" => "",
	"frontend" => "",
	"unique" => false,
	"sort_order" => 220
));

$installer->addAttribute("customer", "loyalty_card_number_3_type", array(
	"type" => "int",
	"backend" => "",
	"label" => "3. Loyalty card number type",
	"input" => "select",
	"source" => "zolagocustomer/source_loyalty_card_types",
	"visible" => true,
	"required" => false,
	"default" => "",
	"frontend" => "",
	"unique" => false,
	"sort_order" => 310
));


$installer->addAttribute("customer", "loyalty_card_number_3_expire", array(
	"type" => "datetime",
	"backend" => "",
	"label" => "3. Loyalty card number expire at",
	"input" => "date",
	"visible" => true,
	"required" => false,
	"default" => "",
	"frontend" => "",
	"unique" => false,
	"sort_order" => 320
));

/* 2. Add attributes to group */
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'loyalty_card_number_1_type',
    '110'  //sort_order
);
$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'loyalty_card_number_1_expire',
	'120'  //sort_order
);

$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'loyalty_card_number_2_type',
	'210'  //sort_order
);
$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'loyalty_card_number_2_expire',
	'220'  //sort_order
);

$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'loyalty_card_number_3_type',
	'310'  //sort_order
);
$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'loyalty_card_number_3_expire',
	'320'  //sort_order
);

/* 3. Set attributes as used_in_forms */
$attribute1Type = $eavConfig->getAttribute("customer", "loyalty_card_number_1_type");
$attribute1Type->setData("used_in_forms", $usedInForms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
	->setData("sort_order", 110);
$attribute1Type->save();

$attribute1Exp = $eavConfig->getAttribute("customer", "loyalty_card_number_1_expire");
$attribute1Exp->setData("used_in_forms", $usedInForms)
	->setData("is_used_for_customer_segment", true)
	->setData("is_system", 0)
	->setData("is_user_defined", 1)
	->setData("is_visible", 1)
	->setData("sort_order", 120);
$attribute1Exp->save();

$attribute2Type = $eavConfig->getAttribute("customer", "loyalty_card_number_2_type");
$attribute2Type->setData("used_in_forms", $usedInForms)
	->setData("is_used_for_customer_segment", true)
	->setData("is_system", 0)
	->setData("is_user_defined", 1)
	->setData("is_visible", 1)
	->setData("sort_order", 210);
$attribute2Type->save();

$attribute2Exp = $eavConfig->getAttribute("customer", "loyalty_card_number_2_expire");
$attribute2Exp->setData("used_in_forms", $usedInForms)
	->setData("is_used_for_customer_segment", true)
	->setData("is_system", 0)
	->setData("is_user_defined", 1)
	->setData("is_visible", 1)
	->setData("sort_order", 220);
$attribute2Exp->save();

$attribute3Type = $eavConfig->getAttribute("customer", "loyalty_card_number_3_type");
$attribute3Type->setData("used_in_forms", $usedInForms)
	->setData("is_used_for_customer_segment", true)
	->setData("is_system", 0)
	->setData("is_user_defined", 1)
	->setData("is_visible", 1)
	->setData("sort_order", 310);
$attribute3Type->save();

$attribute1Exp = $eavConfig->getAttribute("customer", "loyalty_card_number_3_expire");
$attribute1Exp->setData("used_in_forms", $usedInForms)
	->setData("is_used_for_customer_segment", true)
	->setData("is_system", 0)
	->setData("is_user_defined", 1)
	->setData("is_visible", 1)
	->setData("sort_order", 320);
$attribute1Exp->save();

$installer->endSetup();
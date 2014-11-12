<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// Customer Phone Attribute
$setup->addAttribute('customer', 'phone', array(
	'input'         => 'text',
	'type'          => 'varchar',
	'label'         => 'Phone',
	'visible'       => 1,
	'required'      => 0,
	'user_defined'  => 1
));

$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'phone',
	'999'  //sort_order
);

// Customer SMS agreement Attribute
$setup->addAttribute('customer', 'sms_agreement', array(
	'input'         => 'select',
	'type'          => 'int',
	'source'        => 'eav/entity_attribute_source_boolean',
	'label'         => 'SMS agreement',
	'visible'       => 1,
	'required'      => 0,
	'user_defined'  => 1
));

$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'sms_agreement',
	'999'  //sort_order
);

$aAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'phone');
$aAttribute->setData('used_in_forms', array('customer_account_edit'));
$aAttribute->save();

$bAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'sms_agreement');
$bAttribute->setData('used_in_forms', array('customer_account_edit'));
$bAttribute->save();

$cAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'phone');
$cAttribute->setData('used_in_forms', array('adminhtml_customer'));
$cAttribute->save();

$dAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'sms_agreement');
$dAttribute->setData('used_in_forms', array('adminhtml_customer'));
$dAttribute->save();

$setup->endSetup();
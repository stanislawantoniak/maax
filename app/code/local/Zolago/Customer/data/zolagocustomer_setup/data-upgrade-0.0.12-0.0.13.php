<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// Customer payment method attr
$setup->addAttribute('customer', 'payment_method', array(
	'input'         => 'text',
	'type'          => 'varchar',
	'label'         => 'Payment method',
	'visible'       => 1,
	'required'      => 0,
	'user_defined'  => 0
));

$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'payment_method',
	'999'  //sort_order
);

// Customer payment provider attr
$setup->addAttribute('customer', 'payment_provider', array(
	'input'         => 'text',
	'type'          => 'varchar',
	'label'         => 'Payment method provider',
	'visible'       => 1,
	'required'      => 0,
	'user_defined'  => 0
));

$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'payment_provider',
	'999'  //sort_order
);

$setup->endSetup();
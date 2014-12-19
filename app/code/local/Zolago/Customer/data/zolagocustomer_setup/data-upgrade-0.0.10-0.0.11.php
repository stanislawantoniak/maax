<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);



$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'phone',
    '999'  //sort_order
);

// Customer Forget Me Attribute
$setup->addAttribute('customer', 'forget_me', array(
    'input'         => 'select',
    'type'          => 'int',
    'source'        => 'eav/entity_attribute_source_boolean',
    'label'         => 'Forget customer',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1
));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'forget_me',
    '999'  //sort_order
);



$bAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'forget_me');
$bAttribute->setData('used_in_forms', array('customer_account_edit'));
$bAttribute->save();


$dAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'forget_me');
$dAttribute->setData('used_in_forms', array('adminhtml_customer'));
$dAttribute->save();

$setup->endSetup();
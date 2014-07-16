<?php
$this->startSetup();

//Rename "Name" attribute
$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_category','name');
if ($attributeId) {
    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    $attribute->setFrontendLabel("Short name")->save();
}
	
// Add "Long name" attribute
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('catalog_category');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroup = Mage::getModel('eav/entity_attribute_group')->load(4);

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'long_name', array(
    'group'         => 'General Information',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Long name',
    'backend'       => '',
    'visible'       => true,
    'required'      => true,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
 
 $setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroup->getId(),
    'long_name',
    '2'                    
);

$this->endSetup();
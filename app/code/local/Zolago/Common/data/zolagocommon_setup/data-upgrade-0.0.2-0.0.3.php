<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Directory
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
 * @var $this Mage_Core_Model_Resource_Setup
 */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$installer->startSetup();

$setName = 'Additional columns';
$groupName = 'Additional columns';

$entityTypeId = $installer->getEntityTypeId('catalog_product');

if (!$installer->getAttributeSet('catalog_product', $setName))
	Mage::getModel('catalog/product_attribute_set_api')
		->create($setName, $installer->getDefaultAttributeSetId($entityTypeId));


$attributeSetId = $installer->getAttributeSetId($entityTypeId, $setName);

if (!$installer->getAttributeGroup($entityTypeId, $attributeSetId, $groupName))
	$installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 100);

$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);


$data = array(
	array("col1", "col1"),
	array("col2", "col2"),
	array("col3", "col3"),
	array("col4", "col4"),
	array("col5", "col5"),
	array("col6", "col6"),
	array("col7", "col7"),
	array("col8", "col8"),
	array("col9", "col9"),
	array("col10", "col10"),
	array("ext_brand", "Brand"),
	array("ext_category", "Category"),
	array("ext_color", "Color"),
	array("ext_productline", "Product line"),
);


foreach ($data as $item) {
	if (!$installer->getAttribute($entityTypeId, $item[0])) {

		$installer->addAttribute($entityTypeId, $item[0], array(
			'attribute_set'		=> $setName,
			'type'              => 'varchar',
			'backend'           => '',
			'frontend'          => '',
			'label'             => $item[1],
			'input'             => 'text',
			'class'             => '',
			'source'			=> '',
			'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible'           => true,
			'required'          => false,
			'user_defined'      => false,
			'default'           => '',
			'searchable'        => false,
			'filterable'        => false,
			'comparable'        => false,
			'visible_on_front'  => false,
			'unique'            => false,
			'group'             => $groupName
		));

		$installer->updateAttribute('catalog_product', $item[0], 'grid_permission', Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER);
		$installer->updateAttribute('catalog_product', $item[0], 'set_id', $attributeSetId);

		$attributeId = $installer->getAttributeId($entityTypeId, $item[0]);
		$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, 100);
	}
}

$installer->endSetup();

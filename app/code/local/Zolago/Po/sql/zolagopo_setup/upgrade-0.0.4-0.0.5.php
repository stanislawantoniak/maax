<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add need invoice to customer address

$needInvoiceCode = "need_invoice";

$catalogSetup = Mage::getResourceModel('customer/setup', 'core_setup');
/* @var $catalogSetup Mage_Catalog_Model_Resource_Setup */
$attributeData = array(
    'label'					=> 'Need invoice',
    'type'					=> 'int',
    'input'					=> 'select',
	'source'				=> 'eav/entity_attribute_source_boolean',
	'is_user_defined'		=> 1,
	'system'				=> 0,
	'visible'				=> 1,
	'required'				=> 0,
	'position'				=> 150,
	'default'				=> 0
);

$catalogSetup->addAttribute("customer_address", $needInvoiceCode, $attributeData);
$attribute = Mage::getSingleton('eav/config')->getAttribute("customer_address", $needInvoiceCode);
$attribute->setData("used_in_forms", array(
	"adminhtml_customer_address", 
	"customer_address_edit", 
	"customer_register_address"
));
// Make after save to write used in forms data
$attribute->save();

$installer->endSetup();

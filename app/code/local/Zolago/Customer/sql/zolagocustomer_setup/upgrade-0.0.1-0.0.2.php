<?php

/**
 * Add last used payment method
 */

$installer = Mage::getResourceModel("customer/setup", "core_setup");
/* @var $installer Mage_Customer_Model_Resource_Setup */

$installer->startSetup();

$installer->addAttribute("customer", "last_used_payment", array(
    'label'					=> 'Last used shipping method',
    'type'					=> 'text',
    'backend'				=> 'eav/entity_attribute_backend_serialized',
    'input'					=> 'text',
	'user_defined'			=> 1,
	'system'				=> 0,
	'visible'				=> 0,
	'required'				=> 0,
	'position'				=> 160
));

$installer->endSetup();

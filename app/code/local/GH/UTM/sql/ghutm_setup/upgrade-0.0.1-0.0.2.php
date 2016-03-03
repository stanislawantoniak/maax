<?php

/* @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer', 'utm_data', array(
	'type'             => 'static',
	'label'            => 'utm data',
	'visible'          => 0,
	'required'         => 0,
	'input'            => 'text',
	'global'           => 1,
	'visible_on_front' => 0
));

$installer->endSetup();

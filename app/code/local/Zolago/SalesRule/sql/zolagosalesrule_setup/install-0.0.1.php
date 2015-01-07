<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('salesrule/rule'), 
	'rule_payer', 
	array(
		"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
		"length"	=> 2, 
		"unsigned"  => true,
		"nullable"  => false,
		"comment"	=> "Rule Payer",
		"default"	=> Zolago_SalesRule_Model_Rule_Payer::PAYER_VENDOR
	)
);

$installer->endSetup();

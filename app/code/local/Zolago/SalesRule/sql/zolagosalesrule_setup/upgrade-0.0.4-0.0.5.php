<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('salesrule/rule'), 
	'promotion_type', 
	array(
		"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
		"length"	=> 2, 
		"unsigned"  => true,
		"nullable"  => false,
		"comment"	=> "Type of promotion (none,for subscribers,etc.)",
		"default"	=> Zolago_SalesRule_Model_Promotion_Type::PROMOTION_NONE,
	)
);
$installer->getConnection()->addColumn(
	$installer->getTable('salesrule/rule'), 
	'promo_image', 
	array(
		"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
		"length"	=> 255, 
		"nullable"  => true,
		"comment"	=> "Image for promotion",
	)
);

$installer->getConnection()->addColumn(
	$installer->getTable('salesrule/coupon'), 
	'customer_id', 
	array(
		"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
		"length"	=> 11, 
		"unsigned"  => true,
		"nullable"  => true,
		"comment"	=> "Customer Id",
	)
);



$installer->endSetup();

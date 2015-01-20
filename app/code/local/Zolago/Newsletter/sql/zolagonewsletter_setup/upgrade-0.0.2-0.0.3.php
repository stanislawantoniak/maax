<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$newsletterTable = $this->getTable("newsletter/subscriber");
$couponTable = $this->getTable("salesrule/coupon");
$couponIdColumn = "coupon_id";

$installer->getConnection()->addColumn($newsletterTable, $couponIdColumn, array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment" => 'Coupon Id'
));

// Add index
$installer->getConnection()->addIndex(
		$newsletterTable,
		$installer->getIdxName($newsletterTable, array($couponIdColumn)),
		array($couponIdColumn)
);

// Add FK key
$installer->getConnection()->addForeignKey(
		$installer->getFkName($newsletterTable, $couponIdColumn, $couponTable, $couponIdColumn),
		$newsletterTable, $couponIdColumn, $couponTable, $couponIdColumn,
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_NO_ACTION
);

$installer->endSetup();

<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$couponTable = $this->getTable("salesrule/coupon");
$newsletterSentColumn = "newsletter_sent";

$installer->getConnection()->addColumn($couponTable, $newsletterSentColumn, array(
	"type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
	"comment" => 'Newsletter Sent',
	"default" => 0
));

// Add index
$installer->getConnection()->addIndex(
		$couponTable,
		$installer->getIdxName($couponTable, array($newsletterSentColumn)),
		array($newsletterSentColumn)
);

$installer->endSetup();

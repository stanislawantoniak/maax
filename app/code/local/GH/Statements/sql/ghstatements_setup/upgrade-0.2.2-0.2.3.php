<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $this->getTable('ghstatements/statement');
$field = "vendor_invoice_id";

$installer->getConnection()
	->addColumn(
		$table,
		"vendor_invoice_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'comment'   => 'Vendor Invoice ID',
			'nullable'  => true,
			'default'   => null
		)
	);

$otherTable = $installer->getTable("zolagopayment/vendor_invoice");

$installer->getConnection()->addForeignKey(
	$installer->getFkName($table,$field,$otherTable,$field),
	$table,$field,$otherTable,$field,Varien_Db_Ddl_Table::ACTION_SET_NULL,Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();

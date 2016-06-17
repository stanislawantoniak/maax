<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()
	->newTable($installer->getTable('zosloyaltycard/card'))
	->addColumn("card_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
	))
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)

	// vendor_id
	->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		'unsigned'  => true,
		'nullable'  => false
	), 'Vendor ID')
	// email
	->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Email')
	// cart_number
	->addColumn('cart_number', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Loyalty cart number')
	// shop_code
	->addColumn('shop_code', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Short shop code')
	// operator_id
	->addColumn("operator_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => true
	), 'Operator Id')
	// card_type
	->addColumn('card_type', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	))
	// expire_date
	->addColumn('expire_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
	// additional_data (serialized field)
	->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Serialized data')

	// foreign key for vendor
	->addForeignKey(
		$installer->getFkName('zosloyaltycard/card', 'vendor_id', 'udropship/vendor', 'vendor_id'),
		'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);

$installer->endSetup();

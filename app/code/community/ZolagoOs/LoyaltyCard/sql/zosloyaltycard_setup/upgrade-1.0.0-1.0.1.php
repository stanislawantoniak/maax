<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->dropTable($installer->getTable('zosloyaltycard/card'));

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
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'nullable'  => false,
		'default'   => '0',
	), 'Store ID')
	// email
	->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Email')
	// card_number
	->addColumn('card_number', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Loyalty card number')
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
	// additional_information (serialized field)
	->addColumn('additional_information', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
	), 'Serialized data')
	// name
	->addColumn('first_name', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => true,
	))
	// surname
	->addColumn('surname', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => true,
	))
	// telephone number
	->addColumn('telephone_number', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => true,
	))

	// foreign key for vendor
	->addForeignKey(
		$installer->getFkName('zosloyaltycard/card', 'vendor_id', 'udropship/vendor', 'vendor_id'),
		'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);

$installer->endSetup();

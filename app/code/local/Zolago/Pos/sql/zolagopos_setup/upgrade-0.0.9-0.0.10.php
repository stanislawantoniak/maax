<?php
/**
 * Products stock in POS
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posStock = $installer->getTable("zolagopos/stock");

$table = $installer->getConnection()
    ->newTable($posStock)
    // Structure
	->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null,
		array(
			'nullable' => false,
			'identity' => true,
			'primary' => true
		))
	->addColumn("product_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null,
		array(
			'nullable' => false,
			'unsigned' => true
		))
	->addColumn("pos_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null,
		array(
			'nullable' => false
		))
	->addColumn("qty", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4",
		array(
			'nullable' => false
		))
    // Indexes
	->addIndex($installer->getIdxName('zolagopos/stock', array('product_id')),
		array('product_id'))
    ->addIndex($installer->getIdxName('zolagopos/stock', array('pos_id')),
        array('pos_id'))
    // Foreign Keys
	->addForeignKey(
		$installer->getFkName('zolagopos/stock', 'product_id', 'catalog/product', 'entity_id'),
		'product_id', $installer->getTable('catalog/product'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
	)
    ->addForeignKey(
        $installer->getFkName('zolagopos/stock', 'pos_id', 'zolagopos/pos', 'pos_id'),
        'pos_id', $installer->getTable('zolagopos/pos'), 'pos_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$installer->getConnection()->createTable($table);

$installer->endSetup();

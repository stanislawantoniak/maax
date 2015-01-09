<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('zolagosalesrule/relation');

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('relation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Rule Id')
	->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Order item id')
	->addColumn('po_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        'unsigned'  => true,
        ), 'PO item id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('payer', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => Zolago_SalesRule_Model_Rule_Payer::PAYER_VENDOR,
        ), 'Payer')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Rule name')
    
    ->addIndex(
			$installer->getIdxName(
				'salesrule/rule', 
				array('rule_id', 'order_item_id', 'po_item_id')
			),
			array('rule_id', 'order_item_id', 'po_item_id'),
			array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
	)
	->addForeignKey(
		$installer->getFkName('zolagosalesrule/relation', 'rule_id', 'salesrule/rule', 'rule_id'),
        'rule_id', $installer->getTable('salesrule/rule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
	)
	->addForeignKey(
		$installer->getFkName('zolagosalesrule/relation', 'order_item_id', 'sales/order_item', 'item_id'),
        'order_item_id', $installer->getTable('sales/order_item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
	)
	->addForeignKey(
		$installer->getFkName('zolagosalesrule/relation', 'po_item_id', 'udpo/po_item', 'entity_id'),
        'po_item_id', $installer->getTable('udpo/po_item'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
	)
    ->setComment('Order / Po item relation with rule');
$installer->getConnection()->createTable($table);

$installer->endSetup();

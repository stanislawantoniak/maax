<?php
/** @var Zolago_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$conn = $installer->getConnection();
$table = $installer->getTable('sales/payment_transaction');
$conn->addColumn($table,'payment_method',array(
	'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
	'nullable' => false,
	'length'  => 32,
	'comment' => 'Payment method name'
	)
);


$installer->getConnection()->addIndex(
    $table,
    $installer->getIdxName('sales/payment_transaction', array('payment_method')),
    array('payment_method')
);
	

$installer->endSetup();





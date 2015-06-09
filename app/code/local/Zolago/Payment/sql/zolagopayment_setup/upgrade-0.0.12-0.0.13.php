<?php

/**
 * zolago payment allocations table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();


//rma_id column is used only in refunds for determining if refund contains money from RMA refund
$installer->getConnection()
	->addColumn(
		$installer->getTable('zolagopayment/allocation'),
		'rma_id',
		array(
			'nullable'  => true,
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'comment'   => "Rma Id",
			'default'   => null,
			'unsigned'  => true
		)
	);

$installer->endSetup();
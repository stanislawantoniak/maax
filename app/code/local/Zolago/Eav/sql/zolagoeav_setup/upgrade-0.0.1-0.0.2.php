<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = new Mage_Core_Model_Resource_Setup('core_setup');
$installer->startSetup();

$eavTable = $installer->getTable('catalog/eav_attribute');
$setIdColumn = 'set_id';

// Add Indexes
$installer->getConnection()
		->addIndex(
			$eavTable, 
			$installer->getIdxName(
				$eavTable,
				array($setIdColumn)
			), 
			array($setIdColumn)
		);

// Add Foreign Keys
$installer->getConnection()
		->addForeignKey(
			$installer->getFkName(
				$eavTable, $setIdColumn,
				$installer->getTable('eav_attribute_set'),
				'attribute_set_id'
			),
			$eavTable,
			$setIdColumn,
			$installer->getTable('eav_attribute_set'),
			'attribute_set_id',
			Varien_Db_Ddl_Table::ACTION_SET_NULL,
			Varien_Db_Ddl_Table::ACTION_CASCADE
        );

$installer->endSetup();

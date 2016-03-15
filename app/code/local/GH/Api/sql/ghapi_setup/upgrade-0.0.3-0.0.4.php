<?php

/**
 * gh_api_message table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$messageTable = $installer->getTable('ghapi/message');

	// Indexes
	$installer->getConnection()
	    ->addIndex($messageTable, $installer->getIdxName('ghapi_message', array('po_increment_id')),
		array('po_increment_id'));



$installer->endSetup();


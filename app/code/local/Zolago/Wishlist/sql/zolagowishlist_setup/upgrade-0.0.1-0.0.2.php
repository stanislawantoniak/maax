<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Drop unique customer id

$wishlistTable = $installer->getTable("wishlist/wishlist");
$idxName = $installer->getIdxName('wishlist/wishlist', 'customer_id', 
		Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->getConnection()->dropIndex($wishlistTable, $idxName);

$installer->endSetup();

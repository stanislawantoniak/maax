<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Product queue - add index type
 */

$table = $installer->getTable('zolagosolrsearch/queue_item');

$installer->getConnection()->dropForeignKey(
		$this->getTable('zolagosolrsearch/queue_item'),
        $installer->getFkName('zolagosolrsearch/queue_item', 'product_id', 'catalog/product', 'entity_id')
);

$installer->endSetup();

<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Product ac core index
 */

$table = $installer->getTable('zolagosolrsearch/queue_item');

$installer->getConnection()
    ->addIndex($table, $installer->getIdxName($table, array("core_name")), array("core_name"));

$installer->endSetup();

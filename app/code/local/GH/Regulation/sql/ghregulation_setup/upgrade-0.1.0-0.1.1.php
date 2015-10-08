<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addIndex(
        $installer->getTable('ghregulation/regulation_vendor_kind'), //$tableName
        $installer->getIdxName('ghregulation/regulation_vendor_kind', array('vendor_id', 'regulation_kind_id')), //$indexName
        array('vendor_id', 'regulation_kind_id'), //$fields
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE //$indexType
    )
;


$installer->endSetup();
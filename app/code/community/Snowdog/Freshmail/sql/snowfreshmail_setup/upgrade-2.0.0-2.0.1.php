<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('snowfreshmail/api_request'),
    'last_response',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_BLOB,
        'size' => '2M',
        'nullable' => true,
        'comment' => 'Last Response',
    )
);

$installer->endSetup();

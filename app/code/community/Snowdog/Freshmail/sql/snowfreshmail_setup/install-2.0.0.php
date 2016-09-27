<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('snowfreshmail/api_request'))
    ->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Request Id')
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
    ), 'Request Action Name')
    ->addColumn('action_parameters', Varien_Db_Ddl_Table::TYPE_TEXT, '1M', array(
        'nullable'  => false,
    ), 'Request Action Parameters')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Request Created At')
    ->addColumn('processed_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => true,
    ), 'Request Processed At')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable' => false,
        'default' => 'new',
    ), 'Request Status')
    ->addColumn('date_expires', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Request Date Expires')
    ->setComment('Api Requests');
$installer->getConnection()->createTable($table);

$installer->endSetup();

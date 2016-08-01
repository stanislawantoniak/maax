<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('snowfreshmail/custom_data'))
    ->addColumn('subscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Subscriber Id')
    ->addColumn('subscriber_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
    ), 'Serialized Subscriber Data')
    ->setComment('Custom Data')
    ->addForeignKey(
        $installer->getFkName(
            'snowfreshmail/custom_data',
            'subscriber_id',
            'newsletter/subscriber',
            'subscriber_id'
        ),
        'subscriber_id',
        $installer->getTable('newsletter/subscriber'),
        'subscriber_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    );
$installer->getConnection()->createTable($table);

// Reset some configuration values
$where = array();
$where[] = $installer->getConnection()->quoteInto('path LIKE ?', 'snowfreshmail/%');
$where[] = $installer->getConnection()->quoteInto('path NOT IN (?)', array(
    'snowfreshmail/connect/api_key',
    'snowfreshmail/connect/api_secret',
));
$installer->getConnection()->delete(
    $installer->getTable('core_config_data'),
    $where
);

$installer->endSetup();

<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $this->getTable('wfoldstorecustomer/customer');

$table = $this->getConnection()
    ->newTable($tableName)
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'nullable' => false,
            'primary' => true
        ))
    // adres e-mail
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 64,
        array(
            'nullable' => false
        ))
    // czy jest zgoda na mailing
    ->addColumn('is_subscribed', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,
        array(
            'nullable' => false,
            'default' => 0
        ))
    // czy ma konto
    ->addColumn('has_account', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,
        array(
            'nullable' => false,
            'default' => 0
        ))

    ->addIndex(
        $installer->getIdxName(
            $tableName,
            array('email'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('email'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
;

$installer->getConnection()->createTable($table);

$installer->endSetup();
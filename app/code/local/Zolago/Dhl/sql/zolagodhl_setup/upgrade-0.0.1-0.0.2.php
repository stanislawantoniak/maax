<?php

$installer = new Mage_Core_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$tableZip = $installer->getTable("zolagodhl/zip");

$table = $installer->getConnection()
    ->newTable($tableZip)
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
             'identity' => true,
             'unsigned' => true,
             'nullable' => false,
             'primary'  => true,
        ), 'id'
    )
    ->addColumn(
        'zip',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        5, array('nullable' => false), 'ZIP'
    )

    ->setComment('ZIP');
$installer->getConnection()->createTable($table);
$installer->getConnection()->addIndex(
    $installer->getTable('zolagodhl/zip'),
    $installer->getIdxName('zolagodhl/zip', array('zip')),
    array('zip'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
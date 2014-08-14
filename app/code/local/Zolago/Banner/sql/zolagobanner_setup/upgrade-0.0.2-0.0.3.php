<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Banner content
 */

$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagobanner/banner_content'))
    ->addColumn("banner_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'primary' => true,
    ))

    ->addColumn("show", Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        'nullable' => false,
    ))
    ->addColumn("html", Varien_Db_Ddl_Table::TYPE_BLOB)
    ->addColumn("image", Varien_Db_Ddl_Table::TYPE_BLOB)
    ->addColumn("caption", Varien_Db_Ddl_Table::TYPE_BLOB)

    ->addIndex($installer->getIdxName('zolagobanner/banner_content', array('banner_id')),
        array('banner_id'))
    ->addForeignKey(
        $installer->getFkName('zolagobanner/banner_content', 'banner_id', 'zolagobanner/banner', 'banner_id'),
        'banner_id', $installer->getTable('zolagobanner/banner'), 'banner_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
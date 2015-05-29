<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagodropship/vendor_brandshop'))
    ->addColumn(
        'vendor_brandshop_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'identity' => true,
             'nullable' => false,
             'primary'  => true,
        )
    )
    ->addColumn(
        'vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'nullable' => false,
        )
    )
    ->addColumn(
        'brandshop_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'nullable' => false,
        )
    )
    ->addColumn(
        'description', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
             'nullable' => true,
        )
    )
    ->addColumn(
        'can_ask', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,
        array(
             'nullable' => false,
             'default' => false,
        )
    )
    ->addColumn(
        'can_add_product', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,
        array(
             'nullable' => false,
             'default' => false,
        )
    )


    ->addIndex($installer->getIdxName('zolagodropship/vendor_brandshop', array('brandshop_id')), array('brandshop_id'))
    ->addIndex($installer->getIdxName('zolagodropship/vendor_brandshop', array('vendor_id')), array('vendor_id'))
    ->addIndex($installer->getIdxName('zolagodropship/vendor_brandshop', array('can_ask')), array('can_ask'))
    ->addIndex($installer->getIdxName('zolagodropship/vendor_brandshop', array('can_add_product')), array('can_add_product'))

    ->addIndex($installer->getIdxName('zolagodropship/vendor_brandshop', array('vendor_id','brandshop_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('vendor_id','brandshop_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))


    ->addForeignKey(
        $installer->getFkName('zolagodropship/vendor_brandshop', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('zolagodropship/vendor_brandshop', 'brandshop_id', 'udropship/vendor', 'vendor_id'),
        'brandshop_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($table);


$installer->endSetup();




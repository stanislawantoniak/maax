<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = $installer->getConnection()
    ->newTable($installer->getTable('gh_attributerules/gh_attribute_rules'))
    ->addColumn(
        'attribute_rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
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
        'filter', Varien_Db_Ddl_Table::TYPE_TEXT, null,
        array(
            'nullable' => true,
        )
    )
    ->addColumn(
        'column', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => true,
        )
    )
    ->addColumn(
        'value', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => true,
        )
    )
    ->addIndex($installer->getIdxName('gh_attributerules/gh_attribute_rules', array('vendor_id')), array('vendor_id'))
    ->addIndex($installer->getIdxName('gh_attributerules/gh_attribute_rules', array('column')), array('column'))

    ->addForeignKey(
        $installer->getFkName('gh_attributerules/gh_attribute_rules', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('gh_attributerules/gh_attribute_rules', 'column', 'eav/attribute', 'attribute_id'),
        'column', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
;

$installer->getConnection()->createTable($table);

$installer->endSetup();
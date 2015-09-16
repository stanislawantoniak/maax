<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$this
    ->_conn
    ->changeColumn(
        $installer->getTable('gh_attributerules/gh_attribute_rules'),
        'value',
        'value',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT
        )
    );
$installer->endSetup();

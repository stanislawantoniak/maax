<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$eavAttributeSet = $installer->getTable('eav/attribute_set');

/* Add "use_sizebox_list" */
$installer->getConnection()->addColumn(
    $eavAttributeSet,
    'use_sizebox_list',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'When 0 use default view (squares), when 1 use list'
    )
);

$installer->endSetup();
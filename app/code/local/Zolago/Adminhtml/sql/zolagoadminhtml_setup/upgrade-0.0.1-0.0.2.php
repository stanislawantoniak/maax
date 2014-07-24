<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$eavAttributeSet = $installer->getTable('eav/attribute_set');

/* Add "Use to create product */
$installer->getConnection()->addColumn(
    $eavAttributeSet,
    'use_to_create_product',
    array(
         'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
         'nullable' => false,
         'default'  => 0,
         'comment'  => 'Use to create product'
    )
);

$installer->endSetup();
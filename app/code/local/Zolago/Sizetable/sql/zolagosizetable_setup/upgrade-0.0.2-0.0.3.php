<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$sizeTableRule = $installer->getTable("zolagosizetable/sizetable_rule");

/**
 * Rule FK fix
 */

$table = $installer->getConnection();

$table->dropForeignKey($sizeTableRule,
        $installer->getFkName('zolagosizetable/sizetable_rule', 'brand_id', 'zolagosizetable/vendor_brand', 'brand_id'));

    $table->modifyColumn($sizeTableRule, "brand_id", array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true
    ));

    $table->addForeignKey(
        $installer->getFkName('zolagosizetable/sizetable_rule', 'brand_id', 'eav/attribute_option', 'option_id'), $sizeTableRule,
        'brand_id', $installer->getTable('eav/attribute_option'), 'option_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ;

$table = $installer->getConnection();

    $table->addColumn($sizeTableRule, "attribute_set_id", array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'comment' => 'Attribute set ID'
    ));

    $table->addForeignKey(
        $installer->getFkName('zolagosizetable/sizetable_rule', 'attribute_set_id', 'eav/attribute_set', 'attribute_set_id'), $sizeTableRule,
        'attribute_set_id', $installer->getTable('eav/attribute_set'), 'attribute_set_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ;

$installer->endSetup();
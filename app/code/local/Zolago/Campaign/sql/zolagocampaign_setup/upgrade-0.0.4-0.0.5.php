<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocampaign/campaign_placement'))
    ->addColumn("placement_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn("category_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn("campaign_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn("banner_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        'nullable' => false,
    ))

    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
    ), 'Position')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
    ), 'Position')

    ->addIndex($installer->getIdxName('zolagocampaign/campaign_placement', array('placement_id')),
        array('placement_id'))
    ->addIndex($installer->getIdxName('zolagocampaign/campaign_placement', array('vendor_id')),
        array('vendor_id'))
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_placement', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_placement', 'campaign_id', 'zolagocampaign/campaign', 'campaign_id'),
        'campaign_id', $installer->getTable('zolagocampaign/campaign'), 'campaign_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_placement', 'banner_id', 'zolagobanner/banner', 'banner_id'),
        'banner_id', $installer->getTable('zolagobanner/banner'), 'banner_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_placement', 'category_id', 'catalog/category', 'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);

$installer->endSetup();





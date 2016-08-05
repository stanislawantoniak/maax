<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'zospwr/point'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zospwr/point'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'ID')
    // Name
    ->addColumn('name',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        16,
        array(
            'nullable' => false,
            'comment'   => "Paczka w ruchu point name",
        ))
    ->addColumn('is_active',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        16,
        array(
            'nullable' => false,
            "default"		=> 0,
            "comment"		=> "Is active"
        ))
    // PointType
    ->addColumn('type',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        64,
        array(
            'nullable' => true
        ))
    // Postcode
    ->addColumn('postcode',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        8,
        array(
            'nullable' => true
        ))
    // Province
    ->addColumn('province',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        256,
        array(
            'nullable' => true
        ))
    // street
    ->addColumn('street',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        256,
        array(
            'nullable' => true
        ))
    // district
    ->addColumn('district',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        256,
        array(
            'nullable' => true
        ))
    // buildingnumber
    ->addColumn('building_number',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        16,
        array(
            'nullable' => true
        ))
    // town
    ->addColumn('city',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        256,
        array(
            'nullable' => true
        ))
    // latitude
    ->addColumn('latitude',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        32,
        array(
            'nullable' => false
        ))
    // longitude
    ->addColumn('longitude',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        32,
        array(
            'nullable' => false
        ))
    // operatinghours
    ->addColumn('operating_hours', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
        array(
            'nullable' => true
        ))
    // available
    ->addColumn('available', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1,
        array(
            'nullable' => false
        ))
    // location
    ->addColumn('location', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
        array(
            'nullable' => true
        ))
    // status
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 64,
        array(
            'nullable' => false
        ))
    ->addColumn('updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        array(
            "comment"		=> "Updated at"
        ))
    ->addIndex($this->getIdxName('zospwr/point', array('name')),
        array('name'));

$installer->getConnection()->createTable($table);

$installer->endSetup();
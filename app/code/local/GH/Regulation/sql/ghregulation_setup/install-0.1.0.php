<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* 1. Regulation Kind Table */
$tableRegulationKind = $installer->getConnection()
    ->newTable($installer->getTable('ghregulation/regulation_kind'))
    ->addColumn("regulation_kind_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true
    ), 'Regulation Kind ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 250, array(
        'nullable' => false
    ), 'Name');

$installer->getConnection()->createTable($tableRegulationKind);

/* 2. Regulation Type Table */
$tableRegulationType = $installer->getConnection()
    ->newTable($installer->getTable('ghregulation/regulation_type'))
    ->addColumn("regulation_type_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true
    ), 'Regulation Type ID')
    ->addColumn('regulation_kind_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Regulation Kind ID reference')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 250, array(
        'nullable' => false
    ), 'Name')
    ->addIndex(
        $installer->getIdxName('ghregulation/regulation_type', array('regulation_kind_id')), //$indexName
        array('regulation_kind_id') //$fields
    )
    ->addForeignKey(
        $installer->getFkName('ghregulation/regulation_type', 'regulation_kind_id', 'ghregulation/regulation_kind', 'regulation_kind_id'),  //$fkName
        'regulation_kind_id', //$column
        $installer->getTable('ghregulation/regulation_kind'), //$refTable
        'regulation_kind_id', //$refColumn
        Varien_Db_Ddl_Table::ACTION_CASCADE, //$onDelete
        Varien_Db_Ddl_Table::ACTION_CASCADE //$onUpdate
    );

$installer->getConnection()->createTable($tableRegulationType);


/* 3. Regulation Document Table */
$tableRegulationDocument = $installer->getConnection()
    ->newTable($installer->getTable('ghregulation/regulation_document'))
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true
    ), 'Regulation Document ID')
    ->addColumn('regulation_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Regulation Type ID reference')
    ->addColumn('document_link', Varien_Db_Ddl_Table::TYPE_VARCHAR, 250, array(
        'nullable' => false
    ), 'Regulation Document Link')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'Regulation Document Date')
    ->addIndex(
        $installer->getIdxName('ghregulation/regulation_document', array('regulation_type_id')), //$indexName
        array('regulation_type_id') //$fields
    )
    ->addForeignKey(
        $installer->getFkName('ghregulation/regulation_document', 'regulation_type_id', 'ghregulation/regulation_type', 'regulation_type_id'),  //$fkName
        'regulation_type_id', //$column
        $installer->getTable('ghregulation/regulation_type'), //$refTable
        'regulation_type_id', //$refColumn
        Varien_Db_Ddl_Table::ACTION_CASCADE, //$onDelete
        Varien_Db_Ddl_Table::ACTION_CASCADE //$onUpdate
    );

$installer->getConnection()->createTable($tableRegulationDocument);


/* 4. Regulation Vendor Document Table */
$tableRegulationDocumentVendor = $installer->getConnection()
    ->newTable($installer->getTable('ghregulation/regulation_document_vendor'))
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true
    ), 'Regulation Vendor Document ID')
    ->addColumn('regulation_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Regulation Type ID reference')
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default' => 0))
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'Regulation Document Date')
    ->addIndex(
        $installer->getIdxName('ghregulation/regulation_document_vendor', array('regulation_type_id')), //$indexName
        array('regulation_type_id') //$fields
    )
    ->addForeignKey(
        $installer->getFkName('ghregulation/regulation_document_vendor', 'regulation_type_id', 'ghregulation/regulation_type', 'regulation_type_id'),  //$fkName
        'regulation_type_id', //$column
        $installer->getTable('ghregulation/regulation_type'), //$refTable
        'regulation_type_id', //$refColumn
        Varien_Db_Ddl_Table::ACTION_CASCADE, //$onDelete
        Varien_Db_Ddl_Table::ACTION_CASCADE //$onUpdate
    )
    ->addForeignKey(
        $installer->getFkName('ghregulation/regulation_document_vendor', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id',
        $installer->getTable('udropship_vendor'),
        'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($tableRegulationDocumentVendor);


/* 5. Regulation Vendor Kind Table */
$tableRegulationVendorKind = $installer->getConnection()
    ->newTable($installer->getTable('ghregulation/regulation_vendor_kind'))
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true
    ), 'Regulation Vendor Kind ID')
    ->addColumn('regulation_kind_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Regulation Kind ID reference')
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default' => 0))
    ->addIndex(
        $installer->getIdxName('ghregulation/regulation_vendor_kind', array('regulation_kind_id')), //$indexName
        array('regulation_kind_id') //$fields
    )
    ->addForeignKey(
        $installer->getFkName('ghregulation/regulation_vendor_kind', 'regulation_kind_id', 'ghregulation/regulation_kind', 'regulation_kind_id'),  //$fkName
        'regulation_kind_id', //$column
        $installer->getTable('ghregulation/regulation_kind'), //$refTable
        'regulation_kind_id', //$refColumn
        Varien_Db_Ddl_Table::ACTION_CASCADE, //$onDelete
        Varien_Db_Ddl_Table::ACTION_CASCADE //$onUpdate
    )
    ->addForeignKey(
        $installer->getFkName('ghregulation/regulation_vendor_kind', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id',
        $installer->getTable('udropship_vendor'),
        'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($tableRegulationVendorKind);


$installer->endSetup();
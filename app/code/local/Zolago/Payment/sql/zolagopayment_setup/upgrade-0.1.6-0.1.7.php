<?php
/**
 * fakturowanie usług: prowizje i transport
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('udropship/vendor');

$installer->getConnection()
    ->addColumn(
        $table,
        'wfirma_contractor_id',
        array(
            'nullable'  => true,
            'default'   => null,
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'    => 11,
            "comment"   => "wFirma vendor contractor id"
        )
    );

$installer->endSetup();
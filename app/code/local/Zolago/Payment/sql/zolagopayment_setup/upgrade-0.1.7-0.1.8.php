<?php
/**
 * fakturowanie usług: prowizje i transport
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('zolagopayment/vendor_invoice');

$installer->getConnection()
    ->addColumn(
        $table,
        'note',
        array(
            'nullable'  => true,
            'default'   => null,
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            "comment"   => "Invoice Note"
        )
    );

$installer->endSetup();
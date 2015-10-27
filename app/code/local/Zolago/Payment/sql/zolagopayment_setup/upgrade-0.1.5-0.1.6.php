<?php
/**
 * fakturowanie usług: prowizje i transport
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableVendorInvoice = $installer->getTable('zolagopayment/vendor_invoice');

$installer->getConnection()
    ->addColumn(
        $tableVendorInvoice,
        'is_invoice_correction',
        array(
            'nullable'  => false,
            'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            "comment"   => "Czy to jest korekta faktury"
        )
    );

$installer->endSetup();
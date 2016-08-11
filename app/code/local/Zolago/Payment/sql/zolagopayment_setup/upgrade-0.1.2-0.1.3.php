<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();


$installer->getConnection()
    ->modifyColumn(
        $installer->getTable('zolagopayment/vendor_payment'),
        'cost',
        array(
            'nullable'  => false,
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment'   => "Cost",
            'length'    => '12,4'
        )
    );

$installer->endSetup();
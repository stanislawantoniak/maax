<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");
$installer->getConnection()
    ->changeColumn($vendorTable,                    //$tableName
        'confirmation_sent_date',                   //$oldColumnName
        'regulation_confirm_request_sent_date',     //$newColumnName
        'datetime default NULL'                     //$definition
    );
$installer->endSetup();
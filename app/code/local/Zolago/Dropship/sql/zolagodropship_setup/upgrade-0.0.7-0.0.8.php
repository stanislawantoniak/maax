<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, 'confirmation_sent_date', 'datetime default NULL');

$installer->endSetup();
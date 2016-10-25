<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $installer->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, "use_zolagodpd", array(
        "type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
        "lenght" => 1,
        "comment" => "Use DPD"
));

$installer->endSetup();

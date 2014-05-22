<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$poTabel = $this->getTable("udpo/po");
$poTabelGrid = $this->getTable("udpo/po_grid");

// Grand total
$installer->getConnection()->addColumn($poTabel, "grand_total_incl_tax", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,           
	'scale'     => 4,
    'precision' => 12,
	"comment" => "grand_total_incl_tax"
));
$installer->getConnection()->addColumn($poTabelGrid, "grand_total_incl_tax", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,           
	'scale'     => 4,
    'precision' => 12,
	"comment" => "grand_total_incl_tax"
));

$installer->endSetup();

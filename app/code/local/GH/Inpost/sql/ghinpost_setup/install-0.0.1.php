<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$this->getConnection()->addColumn($this->getTable('udpo/po'),'inpost_locker_name',array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment" => "Inpost Locker Name",
	"length" => "16", //currently max length is 8 signs - setting up 16 to ensure compatibility in the future
	"nullable" => true,
	"default" => null
));

$this->endSetup();
<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */


$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"zwrot",
	"auto_days"	=>	0,
	"allowed_days"	=>	0,
	"message"	=> "",
	"visible_on_front" => false,
));


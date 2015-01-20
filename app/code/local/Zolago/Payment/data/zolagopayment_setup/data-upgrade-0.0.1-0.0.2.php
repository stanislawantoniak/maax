<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

// Set all methods to gateway
$installer->
		getConnection()->
		update(
				$installer->getTable("zolagopayment/provider"), 
				array("type"=>"gateway")
		);
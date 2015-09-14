<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$this->getConnection()
	->dropColumn(
		$installer->getTable('zolagocampaign/campaign'),
		'landing_page_url'
	);

$installer->endSetup();
<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install message to db
 */

$message = '<p>
	<span class="bold">
		Twoje zgłoszenie zostało wysłane. 
		Status zgłoszenia możesz na bieżąco śledzić w zakładce zwroty i reklamacje.
	</span>
</p>';

Mage::getConfig()->saveConfig("urma/message/customer_success", $message);
Mage::getConfig()->reinit();
Mage::app()->reinitStores();
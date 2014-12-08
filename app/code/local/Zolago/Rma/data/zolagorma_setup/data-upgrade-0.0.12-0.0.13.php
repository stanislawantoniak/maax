<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install message to db
 */

$message = '<span class="bold">
	Twoje zgłoszenie zostało wysłane. 
	Status zgłoszenia możesz na bieżąco śledzić w zakładce zwroty i reklamacje.
</span>
<br/>
Gdy tylko sprzedawca potwierdzi przyjęcie reklamacji, otrzymasz email z proźbą o uzupełnienie danych.';

Mage::getConfig()->saveConfig("urma/message/customer_success", $message);
Mage::getConfig()->reinit();
Mage::app()->reinitStores();
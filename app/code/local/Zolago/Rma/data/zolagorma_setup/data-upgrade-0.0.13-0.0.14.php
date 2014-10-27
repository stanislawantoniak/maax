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
<span class="bold uppercase">Ważne:</span> Zanim przyjedzie kurier, wydrukuj dokument zwrotu. Formularz zwrotu włóż do przesyłki wraz z produktami, dokument nadania przekaż zaś kurierowi. Dokumenty wyślemy do Ciebie też mailowo.';

Mage::getConfig()->saveConfig("urma/message/customer_success", $message);
Mage::getConfig()->reinit();
Mage::app()->reinitStores();
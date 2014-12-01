<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install pending accept message to db
 */

$data = array(
	array(
		"identifier" => "rma_status_pending_message",
		"title" => "RMA | Pending Accept | Message",
		"content" => '<p>Gdy tylko sprzedawca potwierdzi przyjęcie reklamacji, otrzymasz maila z prośbą o uzupełnienie danych niezbędnych do odesłania produktów.</p>',
		"is_active" => 1,
		"stores" => array(0)
	),
	array(
		"identifier" => "rma_status_pending_booking_message",
		"title" => "RMA | Pending Courier Booking | Message",
		"content" => '<p><span class="bold uppercase">Ważne:</span> Twoja reklamacja została przyjęta przez sklep i będzie realizowana zgodnie z Twoimi oczekiwaniami. Musisz jednak jeszcze uzupełnić brakujące dane - potwierdzić adres i termin, kiedy kurier odbierze od Ciebie paczkę zwrotną.</p>',
		"is_active" => 1,
		"stores" => array(0)
	)
);
foreach($data as $d) {
	Mage::getModel("cms/block")->
	setData($d)->
	save();
}

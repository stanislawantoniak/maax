<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install pending carrier message to db
 */

$data = array(
	"identifier" => "rma_status_pending_currier_message",
	"title" => "RMA | Pending Carrier | Message",
	"content" => '<p><span class="bold uppercase">Ważne:</span> Zanim przyjedzie kurier wydrukuj dokumenty zwrotu. Formularz zwrotu włóż do przesyłki wraz z produktami, dokument nadania przedstaw zaś kurierowi. Dokumenty wyślemy do Ciebie też mailowo.</p>',
	"is_active" => 1,
	"stores" => array(0)
);

Mage::getModel("cms/block")->
	setData($data)->
	save();

<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Update pending carrier message
 */

$data = array(
	"content" => '<p><span class="bold uppercase">Ważne:</span> Zanim przyjedzie kurier wydrukuj dokumenty zwrotu. Formularz zwrotu włóż do przesyłki wraz z produktami, dokument nadania przedstaw zaś kurierowi. Dokumenty wyślemy do Ciebie też mailowo.</p>',
);

Mage::getModel("cms/block")->
	load("rma_status_pending_currier_message")->
	addData($data)->
	save();

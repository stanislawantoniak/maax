<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */


/******************************************************************************
 * Install payments values provided by PayU
 ******************************************************************************/
$dataPayment = array(
	array("c", 1, "Płatność online kartą płatniczą"),
	array("m", 1, "mTransfer"),
	array("n", 1, "MultiTransfer"),
	array("w", 1, "Przelew24 BZWBK"),
	array("o", 1, "Pekao24Przelew"),
	array("h", 1, "Płacę poprzez Przelew z BPH"),
	array("i", 1, "Płacę z Inteligo"),
	array("d", 1, "Płacę z Nordea"),
	array("p", 1, "Płacę z iPKO"),
	array("g", 1, "Płać z ING"),
	array("l", 1, "Crédit Agricole e-przelew"),
	array("wm", 1, "Płacę z Millennium"),
	array("wc", 1, "Płacę z Citi Handlowy"),
	array("wd", 1, "Przelew z Deutsche Bank"),
	array("ib", 1, "Przelew z Idea Bank"),
	array("u", 1, "Płacę z Eurobankiem"),
	array("ab", 1, "Płacę z Alior Bankiem"),
	array("as", 1, "Płacę z Alior Sync"),
	array("ps", 1, "Przelew z PBS"),
	array("me", 1, "MeritumBank Przelew"),
	array("bo", 1, "Płać z BOŚ"),
);

$toInsert = array();
$now = new Zend_Db_Expr("NOW()");

foreach ($dataPayment as $payment) {
	$toInsert[] = array(
		"code" => $payment[0],
		"is_active" => $payment[1],
		"name" => $payment[2],
		"created_at" => $now,
		"updated_at" => $now
	);
}

$installer->getConnection()->
		insertMultiple($installer->getTable("zolagopayment/provider"), $toInsert);

?>

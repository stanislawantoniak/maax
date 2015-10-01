<?php
$postData = array(
	'token' => "testtest123",
	'data' => array(
		array(
			'sku' => '5-13724-BEZOWY',    //products sku
			'date' => '2015-09-30',        //date of clicks
			'cost' => '332',               //price of clicks
			'click_count' => '664',               //clicks amount
			'type' => 'cpc_test'           //cpc type
		),
		array(
			'sku' => '5-15043-BEZOWY',
			'date' => '2015-09-30',
			'cost' => '123',
			'click_count' => '246',
			'type' => 'cpc_test'
		),
		array(
			'sku' => '5-9486',
			'date' => '2015-09-30',
			'cost' => '65',
			'click_count' => '130',
			'type' => 'cpc_test'
		)
	)
);

try {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_USERPWD, 'zolago:kopytko1234');
	curl_setopt($ch, CURLOPT_URL, "https://dev01.lorante.com/ghmarketing/load/index");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$server_output = curl_exec($ch);

	curl_close($ch);

	echo $server_output;
} catch (Exception $e) {
	var_dump($e);
}
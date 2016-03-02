<?php
$postData = array(
	'token' => "testtest123",
	'data' => array(
		array(
			'sku' => '10-08J512-5-523',    //products sku
			'date' => '2016-02-10',        //date of clicks
			'cost' => rand(5,50),          //price of clicks
			'click_count' => rand(1,100),  //clicks amount
			'type' => 'nokaut'             //cpc type
		),
		array(
			'sku' => '10-08J442A4-01',    //products sku
			'date' => '2016-02-10',        //date of clicks
			'cost' => rand(5,50),          //price of clicks
			'click_count' => rand(1,100),  //clicks amount
			'type' => 'skapiec'            //cpc type
		),
		array(
			'sku' => '10-04B121-3-02',    //products sku
			'date' => '2016-02-10',        //date of clicks
			'cost' => rand(5,50),          //price of clicks
			'click_count' => rand(1,100),  //clicks amount
			'type' => 'AN1'                //cpc type
		),
	)
);

try {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_USERPWD, 'zolago:kopytko1234');
	curl_setopt($ch, CURLOPT_URL, "https://test01.lorante.com/ghmarketing/load/index");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	if (!$server_output = curl_exec($ch)) {
		var_dump(curl_error($ch));
	}

	curl_close($ch);

	echo $server_output;
} catch (Exception $e) {
	var_dump($e);
}
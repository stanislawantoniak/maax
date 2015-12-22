<?php

$url = 'http://modago.dev/dotpay/notification/index';
$myvars = "id=471764&operation_number=M1366-1947&operation_type=payment&operation_status=completed&operation_amount=139.00&operation_currency=PLN&operation_original_amount=139.00&operation_original_currency=PLN&operation_datetime=2015-12-18+14%3A15%3A47&control=100000074&description=Order+ID%3A+100000074&email=adam.wilk%40convertica.pl&p_email=tomasz.sypula%40zolago.com&channel=246&signature=75b1d2cbd17b8b257613ec4e69f82d5c40aa279457a861fe1ee6196e13def70a";

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );

var_dump($response);
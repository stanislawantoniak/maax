<?php

$url = 'http://modago.dev/dotpay/notification/index';
$myvars = "id=471764&operation_number=M1147-8957&operation_type=payment&operation_status=completed&operation_amount=139.00&operation_currency=PLN&operation_original_amount=139.00&operation_original_currency=PLN&operation_datetime=2016-01-20+11%3A42%3A28&control=100000236&description=Order+ID%3A+100000236&email=adam.wilk%40convertica.pl&p_email=tomasz.sypula%40zolago.com&channel=6&signature=73f700a1d1e82ac02887caf23b3f7a3d7e9f5976890406228672c4ebe363c273";

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );

var_dump($response);
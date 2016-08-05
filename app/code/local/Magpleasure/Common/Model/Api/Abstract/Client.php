<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Model_Api_Abstract_Client
{

    /**
     * Make abstract post Call to Endpoint
     *
     * @param $contentType
     * @param $apiEndpoint
     * @param $postContent
     * @return bool|mixed
     */
    protected function _call($apiEndpoint, $postContent, $contentType)
    {
        $curlOptions = array(
            CURLOPT_POST         => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER     => array('Content-Type' => $contentType),
            CURLOPT_URL => $apiEndpoint,
            CURLOPT_POSTFIELDS => $postContent,
        );

        $ch = curl_init();
        try {


            curl_setopt_array( $ch, $curlOptions );
            $data = curl_exec( $ch );
            curl_close( $ch );
            return $data;

        } catch (Exception $e){

            curl_close($ch);
            return false;
        }
    }
}
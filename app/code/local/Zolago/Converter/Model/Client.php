<?php

class Zolago_Converter_Model_Client {

    const URL_KEY = "{{key}}";
    const URL_KEY_BATCH = "{{keys}}";
    const PRICE_BATCH_SIZE = 200;

    static protected $_priceRegistry;

    protected $_conf = array();

    /**
     * @return array (
     *	'stock_url'	=> 'string',
     *	'login'		=> 'string',
     *	'password'	=> 'string'
     * )
     */
    public function getConfig($field=null) {
        if(!$this->_conf) {
            $this->_conf = Mage::getStoreConfig("zolagoconverter/config");
        }
        return $field ? trim($this->_conf[$field]) : $this->_conf;
    }

    /**
     * @param string $vendorExternalId
     * @param string $vendorSku
     * @return array | null
     */
    public function getQtys($vendorExternalId, $vendorSku) {
        $key = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
        $url = $this->_replaceUrlKey($this->getConfig('url_stock'), $key);
        $result=$this->_makeConnection($url);
        $out = array();

        if(is_array($result) && isset($result['rows'])) {
            foreach($result['rows'] as $row) {
                if(isset($row['value']['pos']) && isset($row['value']['stock'])) {
                    $out[] = $row['value'];
                }
            }
        }
        if($out) {
            return $out;
        }
        return null;
    }

    /**
     * @param string $vendorExternalId
     * @param string $posExternalId
     * @param string $vendorSku
     * @return int | null
     */
    public function getQty($vendorExternalId, $posExternalId, $vendorSku) {
        $key = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
        $url = $this->_replaceUrlKey($this->getConfig('url_stock'), $key);
        $result=$this->_makeConnection($url);
        if(is_array($result) && isset($result['rows'])) {
            foreach($result['rows'] as $row) {
                if(isset($row['value']['pos']) && isset($row['value']['stock']) &&
                        strtolower(trim($row['value']['pos']))==strtolower(trim($posExternalId))) {
                    return (int)$row['value']['stock'];
                }
            }
        }
        return null;
    }


    /**
     * @param $vendorExternalId
     * @param $vendorProductsData - array("skuv1"=>"A", "skuv2" => "A", ...); keys - skuv, value - price type
     * @return array
     */
    public function getPriceBatch($vendorExternalId, $vendorProductsData)
    {

        $priceBatch = array();

        if (empty($vendorProductsData)) {
            return $priceBatch;
        }

        $urlPriceBatchSize = $this->getConfig('url_price_batch_size');
        $numberQ = !empty($urlPriceBatchSize) ? $urlPriceBatchSize : self::PRICE_BATCH_SIZE;
        //Mage::log(count($vendorProductsData), null, "set_log_4.log");
        if (count($vendorProductsData) >= $numberQ) {
            $priceBatchAll = array();
            $vendorProductsDataBatch = array_chunk($vendorProductsData, $numberQ, true);
            foreach ($vendorProductsDataBatch as $vendorProductsDataBatchItem) {
                $response = $this->getPriceBatchRequest($vendorExternalId, $vendorProductsDataBatchItem);
                if(isset($response[$vendorExternalId])){
                    $priceBatchAll = array_merge($priceBatchAll,$response[$vendorExternalId]);
                }
                unset($response);
            }
            $priceBatch[$vendorExternalId] = $priceBatchAll;
        } else {
            $priceBatch = $this->getPriceBatchRequest($vendorExternalId, $vendorProductsData);
        }

        return $priceBatch;

    }

    public function getPriceBatchRequest($vendorExternalId, $vendorProductsData){
        $priceBatch = array();
        if (empty($vendorProductsData)) { //should FIX http://85.194.243.53:8092/modago/_design/read/_view/price?keys=["5:"]
            return $priceBatch;
        }
        $keyParts = array();
        foreach ($vendorProductsData as $vendorSku => $priceType) {
            if(!empty($vendorSku)){  //should FIX http://85.194.243.53:8092/modago/_design/read/_view/price?keys=["5:"]
                $keyParts[] = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
            }
        }
        if (empty($keyParts)) {
            return $priceBatch;
        }
        $keys = "[" . implode(",", $keyParts) . "]";
        $url = $this->_replaceUrlKey($this->getConfig('url_price_batch'), $keys, self::URL_KEY_BATCH);

        $result = $this->_makeConnection($url);

        if (isset($result['error'])) {
            Mage::log(implode(' ,', $result), null, "getPriceBatchRequestError.log");
            return $priceBatch;
        }

        if (is_array($result) && isset($result['rows'])) {
            foreach ($result['rows'] as $row) {
                if (isset($row['value']['price']) && !empty($row['value']['price'])) {
                    $prices = $row['value']['price'];
                    foreach ($prices as $priceConverterType => $pricesItem) {
                        $vendorSku = explode(":", $row["key"])[1];
                        if (strtoupper($priceConverterType) == strtoupper($vendorProductsData[$vendorSku])) {
                            $priceBatch[$vendorExternalId][$vendorSku] = $pricesItem;
                        }

                    }
                }
            }
        }

        return $priceBatch;
    }

    /**
     * @param $vendorExternalId
     * @param $priceType
     * @param $vendorSku
     *
     * @return null
     */
    public function getPrice($vendorExternalId, $vendorSku, $priceType)
    {
        $priceType = strtoupper($priceType);
        if (!isset(static::$_priceRegistry[$vendorExternalId][$vendorSku][$priceType])) {
            $key = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
            $url = $this->_replaceUrlKey($this->getConfig('url_price'), $key);
            $result = $this->_makeConnection($url);
            if(isset($result['error'])) {
                Mage::log(implode(' ,' , $result));
                return null;
            }
            if (is_array($result) && isset($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    if (isset($row['value']['price']) && !empty($row['value']['price'])) {
                        $prices = $row['value']['price'];
                        foreach ($prices as $priceConverterType => $pricesItem) {
                            static::$_priceRegistry[$vendorExternalId][$vendorSku][strtoupper($priceConverterType)] = $pricesItem;
                        }
                    }
                }
            }
            if (!isset(static::$_priceRegistry[$vendorExternalId][$vendorSku][$priceType])) {
                static::$_priceRegistry[$vendorExternalId][$vendorSku][$priceType] = null;
            }
        }
        return static::$_priceRegistry[$vendorExternalId][$vendorSku][$priceType];
    }

    /**
     * @param $vendorExternalId
     * @param $priceType
     * @param $vendorSku
     *
     * @return null
     */
    public function getPrices($vendorExternalId, $vendorSku)
    {
        $key = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
        $url = $this->_replaceUrlKey($this->getConfig('url_price'), $key);
        $result = $this->_makeConnection($url);

        if(isset($result['error'])) {
            Mage::log(implode(' ,' , $result), null, "converterError.log");
            return null;
        }
        if (is_array($result) && isset($result['rows'])) {
            foreach ($result['rows'] as $row) {
                if (isset($row['value']['price']) && !empty($row['value']['price'])) {
                    return $row['value']['price'];
                }
            }
        }
        return null;
    }

    /**
     * @param $url
     * @param $key
     * @param bool|FALSE $placeholder
     * @return mixed
     */
    protected function _replaceUrlKey($url, $key, $placeholder = FALSE) {
        if($placeholder){
            return str_replace($placeholder, urlencode($key), $url);
        }
        return str_replace(self::URL_KEY, urlencode($key), $url);
    }


    /**
     * @param type $url
     * @return null | string
     */
    protected function _makeConnection($url) {
        $return = null;
        try {
            $process = curl_init($url);
            //Mage::log($url, null, "_makeConnection.log");
            curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($process, CURLOPT_USERPWD, $this->getConfig('login') . ":" . $this->getConfig('password'));
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            curl_setopt($process, CURLOPT_HTTPGET, 1);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            $return = curl_exec($process);

            if(curl_errno($process) > 0){
                Mage::log("CONVERTER CONNECT ERROR NO: " . curl_errno($process));
                if(curl_errno($process) == 56){
                    Mage::log("CONVERTER GET PRICE IS TOO BIG");
                }
            }


            curl_close($process);
        }  catch (Exception $e) {
            Mage::logException($e);
        }

        return Zend_Json::decode($return);
    }

}
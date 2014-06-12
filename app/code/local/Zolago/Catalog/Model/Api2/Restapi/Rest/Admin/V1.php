<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 extends Zolago_Catalog_Model_Api2_Restapi {


    /*test*/
    public function createTest($data)
    {
        $json = json_encode($data);
        $log = Zolago_Catalog_Helper_Log::log($json, TRUE);
        return $log;
    }

    protected function _create($data)
    {

        $json = json_encode($data);
        Mage::log(microtime() . " " . $json, 0, 'converter_log.log');

        if (!empty($data)) {
            $productAction = Mage::getSingleton('catalog/product_action');
            $merchant = $data['merchant'];
            $skuV = $data['sku'];

            $sku = $merchant . '-' . $skuV;

            $productId = Zolago_Catalog_Helper_Data::getSkuAssocId($sku);
            if ($productId) {

                $prices = isset($data['data']) ? $data['data'] : array();
                if(!empty($prices)){

                    $priceA = FALSE;
                    foreach ($prices as $pricesItem) {
                        if ($pricesItem['price_id'] == "A") {
                            $priceA = $pricesItem['price'];
                        }
                    }


                    $productIds = array($productId);
                    $attrData = array('price' => $priceA);

                    $productAction->updateAttributesNoIndex($productIds, $attrData, 0);
                    $productAction->updateAttributesNoIndex($productIds, $attrData, 1);
                    $productAction->updateAttributesNoIndex($productIds, $attrData, 2);

                    Mage::log(microtime() . " " . $sku . ":".$priceA ."\n ---------------" , 0, 'converter_log.log');

                    Zolago_Catalog_Helper_Configurable::queueProduct($productId);
                }


            }
        }
        //Zolago_Catalog_Helper_Log::log($json);
        return $json;
    }


    protected function _retrieveCollection()
    {
        return json_encode(array("testing", "hello2"));
    }

    protected function _retrieve()
    {
        return json_encode($this->getRequest());
        //return json_encode(array("testing", "hello3"));
    }

    protected function _multiUpdate($data)
    {
        $json = json_encode($data);
        Mage::log(microtime() . " " . $json, 0, 'converter_stock_test.log');
        return $json;
    }



}
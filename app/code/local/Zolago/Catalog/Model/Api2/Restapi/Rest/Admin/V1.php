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
        Mage::log($json, 0, 'converter_test.log');

        if (!empty($data)) {
            $productAction = Mage::getSingleton('catalog/product_action');
            $merchant = $data['merchant'];
            $skuV = $data['sku'];

            $sku = $merchant . '-' . $skuV;
            $productId = Zolago_Catalog_Helper_Data::getSkuAssocId($sku);
            if ($productId) {
                $price = $data['data'][0]['price'];

                $productIds = array($productId);
                $attrData = array('price' => $price);

                $productAction->updateAttributesNoIndex($productIds, $attrData, 0);
                $productAction->updateAttributesNoIndex($productIds, $attrData, 1);
                $productAction->updateAttributesNoIndex($productIds, $attrData, 2);

                Zolago_Catalog_Helper_Configurable::queueProduct($productId);
            }

        }
        return $json;
    }

    /*--test*/


//    protected function _create() {
//        return json_encode(array("testing","hello"));
//    }
    protected function _retrieveCollection()
    {
        return json_encode(array("testing", "hello2"));
    }

    protected function _retrieve()
    {
        return json_encode($this->getRequest());
        //return json_encode(array("testing", "hello3"));
    }

    protected function _multiUpdate()
    {

        $skuAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc();

        $data = $this->getRequest()->getBodyParams();

        echo "Start updateAttributes from XML " . date('h:i:s') . " " . microtime() . "<br />";
        if (!empty($data)) {
            $productAction = Mage::getSingleton('catalog/product_action');
            $merchant = $data['merchant'];

            $productsButch = $data['pos'];
            //$productsButch = array_slice($productsButch, 0, 3); //for test

            $idsForQueue = array();
            foreach ($productsButch as $productsButchItem) {
                $skuXML = $productsButchItem['sku'];
                $price = $productsButchItem['price'];
                $sku = $merchant . '-' . $skuXML;


                $productId = isset($skuAssoc[$sku]) ? $skuAssoc[$sku] : 0;
                $idsForQueue[$productId] = $productId;


                if (!empty($productId)) {
                    $productIds = array($productId);
                    $attrData = array('price' => $price);

                    $productAction->updateAttributesNoIndex($productIds, $attrData, 0);
                    //$productAction->updateAttributesNoIndex($productIds, $attrData, 1);
                    //$productAction->updateAttributesNoIndex($productIds, $attrData, 2);
                }
            }
            unset($productsButchItem);
        }
        echo "End updateAttributes from XML " . date('h:i:s') . " " . microtime() . "<br />";


//        Mage::getSingleton('index/indexer')->processEntityAction(
//            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
//        );

        //Zolago_Catalog_Helper_Configurable::queue($idsForQueue);

        return json_encode($this->getRequest());
    }



}
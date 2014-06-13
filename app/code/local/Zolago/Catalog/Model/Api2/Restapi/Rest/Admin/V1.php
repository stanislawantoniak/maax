<?php
/**
 * API2 Model
 *
 * @category   Zolago
 * @package    Zolago_Catalog
 * method string _create() _create(array $data) creation of an entity
 * method void _multiCreate() _multiCreate(array $filteredData) processing and creation of a collection
 * method array _retrieve() retrieving an entity
 * method array _retrieveCollection() retrieving a collection
 * method void _update() _update(array $filteredData) update of an entity
 * method void _multiUpdate() _multiUpdate(array $filteredData) update of a collection
 * method void _delete() deletion of an entity
 * method void _multidelete() _multidelete(array $requestData) deletion of a collection
 */
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1
    extends Zolago_Catalog_Model_Api2_Restapi
{

    public function createTest($data)
    {
        $json = json_encode($data);
        $log = Zolago_Catalog_Helper_Log::log($json, true);
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
                if (!empty($prices)) {

                    $priceA = false;
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

                    Mage::log(microtime() . " " . $sku . ":" . $priceA . "\n ---------------", 0, 'converter_log.log');

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
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
    const CONVERTER_PRICE_UPDATE_LOG = 'converter_profilerPriceBatch.log';
    const CONVERTER_STOCK_UPDATE_LOG = 'converter_profilerStockBatch.log';

    /**
     * @param $data
     *
     * @return bool|int
     */
    public function createTest($data)
    {
        $json = json_encode($data);
        $log = Zolago_Catalog_Helper_Log::log($json, true);
        return $log;
    }

    public function api2($data)
    {
        $this->_create($data);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function _create($data)
    {

        $json = json_encode($data);
        Mage::log($json, 0, 'converter_log.log');

        if (!empty($data)) {
            foreach ($data as $cmd => $batch) {
                switch ($cmd) {
                    case 'ProductPricesUpdate':
                        $priceBatch = array();
                        if(!empty($batch)){
                            $batch = (array)$batch;
                            foreach($batch as $dataPrice){
                                $merchant = $dataPrice['merchant'];
                                $prices = $dataPrice['data'];

                                if (!empty($prices)) {
                                    foreach ($prices as $skuV => $priceByType) {
                                        $sku = $merchant . "-" . $skuV;
                                        $priceBatch[$sku] = $priceByType;
                                    }
                                }
                                unset($sku);
                                unset($skuV);
                                unset($priceByType);
                            }
                            unset($dataPrice);
                        }

                        self::updatePricesConverter($priceBatch);
                        break;
                    case 'ProductStockUpdate':
                        $batchFile = self::CONVERTER_STOCK_UPDATE_LOG;
                        //Mage::log(microtime() . ' Start', 0, $batchFile);

                        $stockBatch = array();

                        if(!empty($batch)){
                            $batch = (array)$batch;
                            foreach($batch as $dataStock){
                                $merchant = $dataStock['merchant'];
                                $stock = $dataStock['data'];

                                if (!empty($stock)) {
                                    foreach ($stock as $skuV => $stockByPOS) {
                                        $sku = $merchant . "-" . $skuV;
                                        $stockBatch[$merchant][$sku] = $stockByPOS;
                                    }
                                }
                                unset($sku);
                                unset($skuV);
                                unset($stockByPOS);
                            }
                            unset($dataStock);
                        }

                        self::updateStockConverter($stockBatch);
                        break;
                    default:
                        //
                }
            }
            unset($cmd);unset($batch);
        }
        return $json;
    }


    /**
     * @return string
     */
    protected function _retrieveCollection()
    {
        return json_encode(array("testing", "hello2"));
    }

    /**
     * @return string
     */
    protected function _retrieve()
    {
        return json_encode($this->getRequest());
        //return json_encode(array("testing", "hello3"));
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function _multiUpdate($data)
    {
        $json = json_encode($data);
        Mage::log(microtime() . " " . $json, 0, 'converter_stock_test.log');
        return $json;
    }

    /**
     * 1. Update stock item
     * 2. Run indexer
     * 3. Move to solr que
     * @param $stockBatch
     */
    public static function updateStockConverter($stockBatch)
    {

        if (empty($stockBatch)) {
            return;
        }
        $skuS = array();
        foreach ($stockBatch as $stockBatchItem) {
            $skuS = array_merge($skuS, array_keys($stockBatchItem));
        }

        $stockId = 1;

        $cataloginventoryStockItem = array();

        $availableStockByMerchant = array();
        foreach ($stockBatch as $merchant => $stockData) {
            $s = Zolago_Catalog_Helper_Stock::getAvailableStock($stockData, $merchant);
            $availableStockByMerchant = $s + $availableStockByMerchant;
        }

        /*Prepare data to insert*/
        if (empty($availableStockByMerchant)) {
            return;
        }

        if (!empty($availableStockByMerchant)) {
            foreach ($availableStockByMerchant as $id => $qty) {
                $is_in_stock = ($qty > 0) ? 1 : 0;
                $cataloginventoryStockItem [] = "({$id},{$qty},{$is_in_stock},{$stockId})";

                Mage::dispatchEvent("zolagocatalog_converter_stock_save_before", array(
                    "product_id" => $id,
                    "qty" => $qty,
                    "is_in_stock" => $is_in_stock,
                    "stock_id" => $stockId
                ));
            }
        }

        $insert = implode(',', $cataloginventoryStockItem);

        $zcSDItemModel = Mage::getResourceModel('zolago_cataloginventory/stock_item');
        $zcSDItemModel->saveCatalogInventoryStockItem($insert);

        $productsIds = Mage::getResourceModel("catalog/product_collection")
            ->addAttributeToFilter('sku', array('in' => $skuS))
            ->getAllIds();

        Mage::getResourceModel('cataloginventory/indexer_stock')
            ->reindexProducts($productsIds);

        Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array());
    }

    /**
     * @param $priceBatch
     */
    public static function updatePricesConverter($priceBatch){
        $batchFile = self::CONVERTER_PRICE_UPDATE_LOG;
        $skuS = array_keys($priceBatch);
        $itemsToChange = count($skuS);
        //Mage::log($priceBatch, 0, $batchFile);
        Mage::log(microtime() . " Got items from converter {$itemsToChange}", 0, $batchFile);

        //Get price types
        if(empty($priceBatch)){
            return;
        }
        $skeleton = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);

        if(empty($skeleton)){
            return;
        }

        $model = Mage::getResourceModel('zolagocatalog/product');

        $productEt = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

        $priceTypeByStore = array();
        $priceType = $model->getConverterPriceType($skuS);
        //reformat by store id
        if (!empty($priceType)) {
            foreach ($priceType as $priceTypeData) {
                $priceTypeByStore[$priceTypeData['sku']][$priceTypeData['store']]
                    = $priceTypeData['price_type'];
            }
        }

        $marginByStore = array();
        $priceMarginValues = $model->getPriceMarginValues($skuS);
        //Mage::log(microtime() . " priceMarginValues: ".print_r($priceMarginValues,true), 0, $batchFile);
        //reformat margin
        if (!empty($priceMarginValues)) {
            foreach ($priceMarginValues as $_) {
                $marginByStore[$_['product_id']][$_['store']] = $_['price_margin'];
            }
            unset($_);
        }


        $insert = array();
        $ids = array();

        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $stores[] = $_storeId;
        }

        //Mage::log(microtime() . ' Start update', 0, $batchFile);
        if (!empty($skeleton)) {
            foreach ($skeleton as $sku => $productId) {

                foreach ($stores as $storeId) {

                    //price type
                    $priceTypeSelected = "A";
                    if (isset($priceTypeByStore[$sku][$storeId])) {
                        $priceTypeSelected = $priceTypeByStore[$sku][$storeId];
                    } else {
                        $priceTypeDefault = isset($priceTypeByStore[$sku][Mage_Core_Model_App::ADMIN_STORE_ID])
                            ? $priceTypeByStore[$sku][Mage_Core_Model_App::ADMIN_STORE_ID] : $priceTypeSelected;
                        $priceTypeSelected = $priceTypeDefault;
                    }


                    $pricesConverter = isset($priceBatch[$sku]) ? (array)$priceBatch[$sku] : false;

                    if ($pricesConverter) {
                        $priceToInsert = isset($pricesConverter[$priceTypeSelected])
                            ? $pricesConverter[$priceTypeSelected] : false;


                        if($priceToInsert){

                            //margin
                            $marginSelected = 0;

                            if (isset($marginByStore[$productId][$storeId])) {
                                $marginSelected = (int)$marginByStore[$productId][$storeId];
                            } else {
                                $marginDefault = isset($marginByStore[$productId][Mage_Core_Model_App::ADMIN_STORE_ID])
                                    ? $marginByStore[$productId][Mage_Core_Model_App::ADMIN_STORE_ID] : $marginSelected;
                                $marginSelected = (int)$marginDefault;
                            }

                            $insert[] = array(
                                'entity_type_id' => $productEt,
                                'attribute_id' => 75,
                                'store_id' => $storeId,
                                'entity_id' => $productId,
                                'value' => Mage::app()->getLocale()->getNumber($priceToInsert + (($priceToInsert * $marginSelected)/100))
                            );
                        }
                    }



                }

                $ids[] = $productId;
            }
        }


        if (!empty($insert)) {
            $model->savePriceValues($insert, $ids);
        }
    }
}
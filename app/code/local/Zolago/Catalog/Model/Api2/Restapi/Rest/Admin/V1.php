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
     *
     * data example
     *
     * {"ProductPricesUpdate":[{"merchant":"5","data":{"25768-L":{"A":31.9,"B":32.9},"25768-M":{"A":31.9,"B":32.9},
     * "25768-XL":{"A":31.9,"B":32.9},"25767-XXL":{"A":31.9,"B":32.9},"25767-XL":{"A":31.9,"B":32.9},
     * "25768-S":{"A":31.9,"B":32.9},"25767-S":{"A":31.9,"B":32.9}}}]}     *
     *
     * @param array $data
     *
     * @return string
     */
    protected function _create($data)
    {

        $json = json_encode($data);
        Mage::log($json, 0, 'json.log');
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
        $availableStockByMerchant = array();
        //Mage::log(print_r($stockBatch, true), 0, "updateStockConverter.log");
        foreach ($stockBatch as $merchant => $stockData) {
            $s = Zolago_Catalog_Helper_Stock::getAvailableStock($stockData); //return array("sku" => qty, ...)
            $availableStockByMerchant = $s + $availableStockByMerchant;
        }
        //Mage::log(print_r($availableStockByMerchant, true), 0, "availableStockByMerchant.log");
        if (empty($availableStockByMerchant)) {
            return;
        }

        $productIdsSkuAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);
        //2. calculate stock on open orders
        $zcSDModel = Mage::getResourceModel('zolagopos/pos');
        $openOrdersQty = $zcSDModel->calculateStockOpenOrders($merchant, $skuS);
        //Mage::log(print_r($openOrdersQty, true), 0, "openOrdersQty.log");


        $availableStockByMerchantOnOpenOrders = array();
        foreach ($availableStockByMerchant as $sku => $availableStockByMerchantQty) {
            //$productIdsSkuAssoc[$sku] product_id
            //$openOrdersQty[$sku] products qty on open orders
            if (isset($productIdsSkuAssoc[$sku])) {
                $qtyOnOpenOrders = isset($openOrdersQty[$sku]) ? $openOrdersQty[$sku]['qty'] : 0;
                $availableStockByMerchantOnOpenOrders[$productIdsSkuAssoc[$sku]] = (($availableStockByMerchantQty - $qtyOnOpenOrders) > 0 ) ? ($availableStockByMerchantQty - $qtyOnOpenOrders) : 0;
            }
        }
        unset($qtyOnOpenOrders);
        unset($availableStockByMerchantQty);
        //Mage::log(print_r($availableStockByMerchantOnOpenOrders, true), 0, "availableStockByMerchantOnOpenOrders.log");

        /*Prepare data to insert*/
        if (empty($availableStockByMerchantOnOpenOrders)) {
            return;
        }

        $productsIds = array();

        $cataloginventoryStockItem = array();
        if (!empty($availableStockByMerchantOnOpenOrders)) {
            foreach ($availableStockByMerchantOnOpenOrders as $id => $qty) {
                $is_in_stock = ($qty > 0) ? 1 : 0;
                $cataloginventoryStockItem [] = "({$id},{$qty},{$is_in_stock},{$stockId})";

                $productsIds[$id] = $id;

                Mage::dispatchEvent("zolagocatalog_converter_stock_save_before", array(
                    "product_id" => $id,
                    "qty" => $qty,
                    "is_in_stock" => $is_in_stock,
                    "stock_id" => $stockId
                ));
            }
        }
        if (empty($cataloginventoryStockItem)) {
            return;
        }

        $insert = implode(',', $cataloginventoryStockItem);

        $zcSDItemModel = Mage::getResourceModel('zolago_cataloginventory/stock_item');
        $zcSDItemModel->saveCatalogInventoryStockItem($insert);

        //reindex
        Mage::getResourceModel('cataloginventory/indexer_stock')
            ->reindexProducts($productsIds);

        //send to solr queue
        Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array());
    }

    /**
     * @param $priceBatch
     */
    public static function updatePricesConverter($priceBatch)
    {
        //queue inform_magento
        $batchFile = self::CONVERTER_PRICE_UPDATE_LOG;
        $skuS = array_keys($priceBatch);
        $itemsToChange = count($skuS);
        Mage::log('Got items ' . $itemsToChange, 0, $batchFile);

        if (empty($priceBatch)) {
            return;
        }
        $skeleton = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);

        if (empty($skeleton)) {
            return;
        }

        $model = Mage::getResourceModel('zolagocatalog/product');

        //converter_price_type from configurable products
        $priceType = $model->getConverterPriceTypeConfigurable($skuS);

        //price_margin from configurable products
        $priceMarginValues = $model->getPriceMarginValuesConfigurable($skuS);

        //converter_msrp_type from configurable products
        $priceMSRPSource = $model->getMSRPSourceValuesUpdateConverterConfigurable($skuS);
        Mage::log(print_r($priceMSRPSource,true), 0, 'priceMSRPSource.log');

        if (empty($priceType) && empty($priceMarginValues) && empty($priceMSRPSource)) {
            return;
        }

        //reformat $priceType, $priceMarginValues, $priceMSRPSource by store
        //1. reformat by store_id $priceType
        $priceTypeByStore = array();
        if (!empty($priceType)) {
            foreach ($priceType as $priceTypeData) {
                $priceTypeByStore[$priceTypeData['sku']][$priceTypeData['store']]
                    = $priceTypeData['price_type'];
            }
        }
        //2. reformat by store_id $priceMarginValues
        $marginByStore = array();
        if (!empty($priceMarginValues)) {
            foreach ($priceMarginValues as $_) {
                $marginByStore[$_['product_id']][$_['store']] = $_['price_margin'];
            }
            unset($_);
        }
        //3. reformat by store_id $priceMSRPSource
        $priceMSRPTypeByStore = array();
        if (!empty($priceMSRPSource)) {
            foreach ($priceMSRPSource as $priceMSRPSourceData) {
                $priceMSRPTypeByStore[$priceMSRPSourceData['sku']][$priceMSRPSourceData['store']]
                    = $priceMSRPSourceData['msrp_source_type'];
            }
        }
        Mage::log(print_r($priceMSRPTypeByStore,true), 0, 'priceMSRPTypeByStore.log');

        $insert = array();
        $ids = array();

//        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        $stores = array();
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $stores[] = $_storeId;
        }

        //product entity_type_id
        $productEt = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

        //price attribute code
        $priceAttributeId = Mage::getSingleton("eav/config")
            ->getAttribute('catalog_product', 'price')
            ->getData('attribute_id');

        //special_price attribute code
        $specialPriceAttributeId = Mage::getSingleton("eav/config")
            ->getAttribute('catalog_product', 'special_price')
            ->getData('attribute_id');


        foreach ($skeleton as $sku => $productId) {
            foreach ($stores as $storeId) {
                //price type default
                $priceTypeSelected = ""; //Manual price
                $priceMSRPSelected = ""; //Manual special_price
                if (isset($priceTypeByStore[$sku][$storeId])) {
                    $priceTypeSelected = $priceTypeByStore[$sku][$storeId];
                }
                if (isset($priceMSRPTypeByStore[$sku][$storeId])) {
                    $priceMSRPSelected = Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_SOURCE;
                }

                if (!empty($priceTypeSelected) || !empty($priceMSRPSelected)) {
                    $pricesConverter = isset($priceBatch[$sku]) ? (array)$priceBatch[$sku] : false;

                    if ($pricesConverter) {
                        $priceToInsert = isset($pricesConverter[$priceTypeSelected])
                            ? $pricesConverter[$priceTypeSelected] : false;  //price

                        $priceMSRPToInsert = isset($pricesConverter[$priceMSRPSelected])
                            ? $pricesConverter[$priceMSRPSelected] : false;  //special_price
                        Mage::log(print_r($priceMSRPToInsert,true), 0, 'priceMSRPToInsert.log');


                        // 1. update price
                        if ($priceToInsert) {
                            //margin
                            $marginSelected = 0;
                            //implement margin
                            if (isset($marginByStore[$productId][$storeId])) {
                                $marginSelected = (float)str_replace(",", ".", $marginByStore[$productId][$storeId]);
                            }

                            $insert[] = array(
                                'entity_type_id' => $productEt,
                                'attribute_id' => $priceAttributeId,
                                'store_id' => $storeId,
                                'entity_id' => $productId,
                                'value' => Mage::app()->getLocale()->getNumber($priceToInsert + (($priceToInsert * $marginSelected) / 100))
                            );

                        }

                        // 2. update special_price
                        if ($priceMSRPToInsert) {
                            $insert[] = array(
                                'entity_type_id' => $productEt,
                                'attribute_id' => $specialPriceAttributeId,
                                'store_id' => $storeId,
                                'entity_id' => $productId,
                                'value' => Mage::app()->getLocale()->getNumber($priceMSRPToInsert)
                            );
                        }
                        $ids[$productId] = $productId;
                    }
                }
            }
        }
        if (empty($insert)) {
            //no data to update
            return;
        }

        //Save price
        $model->savePriceValues($insert, $ids);
    }


}
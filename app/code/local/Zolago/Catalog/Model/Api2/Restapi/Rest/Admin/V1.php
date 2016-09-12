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
                            $merchant = 0;
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
            $s = Zolago_Catalog_Helper_Stock::getAvailableStock($stockData, $merchant); //return array("sku" => qty, ...)
            $availableStockByMerchant = $s + $availableStockByMerchant;
        }
        //Mage::log(print_r($availableStockByMerchant, true), 0, "availableStockByMerchant.log");
        if (empty($availableStockByMerchant)) {
            return;
        }

        $productIdsSkuAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);
        //2. calculate stock on open orders (reservation)

        /* @var $zcSDModel  Zolago_Pos_Model_Resource_Pos */
        $zcSDModel = Mage::getResourceModel('zolagopos/pos');
        $openOrdersQty = $zcSDModel->calculateStockOpenOrders($merchant, $skuS); //reservation


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

        /*Prepare data to insert*/
        if (empty($availableStockByMerchantOnOpenOrders)) {
            return;
        }

        $productsIds = array();

        $productsIdsForSolrAndVarnishBan = array();

        $cataloginventoryStockItem = array();
        if (!empty($availableStockByMerchantOnOpenOrders)) {
            $collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
            $productIds = array_keys($availableStockByMerchantOnOpenOrders);
            $collection->addProductsFilter($productIds);

            $stocks = [];
            $backorders = [];
            foreach ($collection as $val) {                
                $stocks[$val->getProductId()] = (int)$val->getIsInStock();
                $backorders[$val->getProductId()] = (int)$val->getBackorders();
            }            

            foreach ($availableStockByMerchantOnOpenOrders as $id => $qty) {
                if ($backorders[$id]) {
                    //in backorder case just inherit is_in_stock
                    $is_in_stock = $stocks[$id];
                } else {
                    $is_in_stock = ($qty > 0) ? 1 : 0;
                }

                $cataloginventoryStockItem [] = "({$id},{$qty},{$is_in_stock},{$stockId})";


                $productsIds[$id] = $id;
                if ($stocks[$id] != $is_in_stock) {
                    Mage::dispatchEvent("zolagocatalog_converter_stock_save_before", array(
                        "product_id" => $id,
                        "qty" => $qty,
                        "stock_id" => $stockId
                    ));
                    $productsIdsForSolrAndVarnishBan[$id] = $id;
                };
            }
        }
        if (empty($cataloginventoryStockItem)) {
            return;
        }
        if (empty($productsIds)) {
            return;
        }

        /*  @var $zcSDItemModel Zolago_CatalogInventory_Model_Resource_Stock_Item */
        $zcSDItemModel = Mage::getResourceModel('zolago_cataloginventory/stock_item');
        $zcSDItemModel->saveCatalogInventoryStockItem($cataloginventoryStockItem);

        //reindex
        Mage::getResourceModel('cataloginventory/indexer_stock')
            ->reindexProducts($productsIds);

        // Varnish & Turpentine
        $coll = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productsIdsForSolrAndVarnishBan);

        //send to solr queue & ban url in varnish
        Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $coll));
    }


    /**
     * @param $skeleton
     * @param $priceBatch
     */
    public static function saveExternalPriceAttributes($skeleton, $priceBatch)
    {
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        $priceLabels = array("A", "B", "C", "Z", "salePriceBefore");
        foreach ($skeleton as $sku => $id) {
            $attrData = array();

            foreach ($priceLabels as $priceLabel) {
                if (!empty(isset($priceBatch[$sku][$priceLabel]) && (float)$priceBatch[$sku][$priceLabel]) > 0)
                    $attrData["external_price_{$priceLabel}"] = (string)$priceBatch[$sku][$priceLabel];
            }

            $aM->updateAttributesPure(array($id), $attrData, 0);
        }
    }

    /**
     * @param $priceBatch
     */
    public static function updatePricesConverter($priceBatch)
    {
        //queue inform_magento
        $skuS = array_keys($priceBatch);

        if (empty($priceBatch)) {
            return;
        }

        $skeleton = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);

        if (empty($skeleton))
            return;

        self::saveExternalPriceAttributes($skeleton, $priceBatch);

        /* @var $model Zolago_Catalog_Model_Resource_Product */
        $model = Mage::getResourceModel('zolagocatalog/product');


        $notVisibleIndividuallySkus = []; $visibleIndividuallySkus = [];

        $collection1 = Mage::getResourceModel("catalog/product_collection");
        $collection1->addAttributeToFilter("sku", array("in" => $skuS));
        $collection1->addAttributeToFilter("visibility", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $collection1Data = $collection1->getData();
        foreach ($collection1Data as $collection1DataItem){
            $notVisibleIndividuallySkus[] = $collection1DataItem["sku"];
        }
        unset($collection1DataItem);

        $collection2 = Mage::getResourceModel("catalog/product_collection");
        $collection2->addAttributeToFilter("sku", array("in" => $skuS));
        $collection2->addAttributeToFilter("visibility", array("neq" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collection2Data = $collection2->getData();
        foreach ($collection2Data as $collection2DataItem){
            $visibleIndividuallySkus[] = $collection2DataItem["sku"];
        }


        $priceType = []; $priceMarginValues = []; $priceMSRPSourceManual= [];
        if(!empty($notVisibleIndividuallySkus)){
            //converter_price_type from configurable products
            $priceType = $model->getConverterPriceTypeConfigurable($skuS);

            //price_margin from configurable products
            $priceMarginValues = $model->getPriceMarginValuesConfigurable($skuS);

            //converter_msrp_type from configurable products
            $priceMSRPSourceManual = $model->getMSRPSourceValuesManualConverterConfigurable($skuS);
        }
        if(!empty($visibleIndividuallySkus)){
            //converter_price_type from simple products
            $priceType = $model->getConverterPriceTypeSimple($visibleIndividuallySkus);

            //price_margin from simple products
            $priceMarginValues = $model->getPriceMarginValuesSimple($visibleIndividuallySkus);

            //converter_msrp_type from simple products
            $priceMSRPSourceManual = $model->getMSRPSourceValuesManualConverterSimple($visibleIndividuallySkus);
        }


        if (empty($priceType) && empty($priceMarginValues) && empty($priceMSRPSourceManual)) {
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
        if (!empty($priceMSRPSourceManual)) {
            foreach ($priceMSRPSourceManual as $priceMSRPSourceData) {
                $priceMSRPTypeByStore[$priceMSRPSourceData['sku']][$priceMSRPSourceData['store']]
                    = $priceMSRPSourceData['msrp_source_type'];
            }
        }

        $insert = array();
        $ids = array();


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

        //msrp attribute code
        $specialPriceAttributeId = Mage::getSingleton("eav/config")
            ->getAttribute('catalog_product', 'msrp')
            ->getData('attribute_id');


        foreach ($skeleton as $sku => $productId) {
            foreach ($stores as $storeId) {
                //price type default
                $priceTypeSelected = ""; //Manual price
                $priceMSRPSelected = ""; //Manual msrp
                if (isset($priceTypeByStore[$sku][$storeId])) {
                    $priceTypeSelected = $priceTypeByStore[$sku][$storeId];
                }
                if (!isset($priceMSRPTypeByStore[$sku][$storeId])) {
                    $priceMSRPSelected = Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_SOURCE;
                }

                if (!empty($priceTypeSelected) || !empty($priceMSRPSelected)) {

                    $pricesConverter = isset($priceBatch[$sku]) ? (array)$priceBatch[$sku] : false;

                    if ($pricesConverter) {
                        $priceToInsert = isset($pricesConverter[$priceTypeSelected])
                            ? $pricesConverter[$priceTypeSelected] : false;  //price

                        $priceMSRPToInsert = (!empty($priceMSRPSelected) && isset($pricesConverter[$priceMSRPSelected]))
                            ? $pricesConverter[$priceMSRPSelected] : false;  //msrp


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

                        // 2. update msrp
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
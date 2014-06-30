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
        Mage::log($data, 0, 'converter_log.log');
        $json = json_encode($data);

        if (!empty($data)) {
            foreach ($data as $cmd => $batch) {
                switch ($cmd) {
                    case 'ProductPricesUpdate':
                        $batchFile = self::CONVERTER_PRICE_UPDATE_LOG;
                        Mage::log(microtime() . ' Start', 0, $batchFile);
                        Mage::log(microtime() . " {$json}", 0, $batchFile);

                        Mage::log(microtime() . ' Get prices array', 0, $batchFile);
                        $priceBatch = array();
                        if(!empty($batch)){
                            $batch = (array)$batch;
                            foreach($batch as $dataPrice){
                                $merchant = $dataPrice->merchant;
                                $prices = $dataPrice->data;

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
                        Mage::log(microtime() . ' Start', 0, $batchFile);
                        Mage::log(microtime() . " {$json}", 0, $batchFile);

                        Mage::log(microtime() . ' Get stock array', 0, $batchFile);

                        $stockBatch = array();

                        if(!empty($batch)){
                            foreach($batch as $dataStock){
                                $merchant = $dataStock->merchant;
                                $stock = $dataStock->data;

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
     * @param $stockBatch
     */
    public static function updateStockConverter($stockBatch){
        $batchFile = self::CONVERTER_STOCK_UPDATE_LOG;
        if(empty($stockBatch)){
            Mage::log(microtime() . ' Empty source', 0, $batchFile);
            return;
        }
        $skuS = array();
        foreach($stockBatch as $stockBatchItem){
            $skuS = array_merge($skuS, array_keys($stockBatchItem));
        }
        $itemsToChange = count($skuS);
        Mage::log(microtime() . " Got items from converter {$itemsToChange}", 0, $batchFile);
        $stockId = 1;
        $websiteAdmin = 0;
        $websiteFront = 1;

        $cataloginventoryStockItem = array();
        $cataloginventoryStockStatus0 = array();
        $cataloginventoryStockStatus1 = array();

        Mage::log(microtime() . ' Get price types', 0, $batchFile);
        $availableStockByMerchant = array();
        foreach($stockBatch as $merchant => $stockData){
            $s = Zolago_Catalog_Helper_Stock::getAvailableStock($stockData,$merchant);
            $availableStockByMerchant = $s + $availableStockByMerchant;
        }

        /*Prepare data to insert*/
        if(empty($availableStockByMerchant)){
            Mage::log(microtime() . ' Empty source ', 0, $batchFile);
            return;
        }

        if(!empty($availableStockByMerchant)){
            foreach($availableStockByMerchant as $id => $qty){
                $is_in_stock = ($qty > 0) ? 1 : 0;
                $cataloginventoryStockStatus0 []= "({$id},{$qty},{$is_in_stock},{$stockId},{$websiteAdmin})";
                $cataloginventoryStockStatus1 []= "({$id},{$qty},{$is_in_stock},{$stockId},{$websiteFront})";

                $cataloginventoryStockItem []= "({$id},{$qty},{$is_in_stock},{$stockId})";
            }
        }

        $insert1 = implode(',',$cataloginventoryStockItem);
        $insertA = implode(',',$cataloginventoryStockStatus0);
        $insertB = implode(',',$cataloginventoryStockStatus1);

        Mage::log(microtime() . ' End prepare data ', 0, 'product_stock_update.log');
        $zcSDItemModel = Mage::getResourceModel('zolago_cataloginventory/stock_item');

        Mage::log(microtime() . ' Start cataloginventory_stock_item ', 0, 'product_stock_update.log');
        $zcSDItemModel->saveCatalogInventoryStockItem($insert1);

        $zcSDStatusModel = Mage::getResourceModel('zolago_cataloginventory/stock_status');
        Mage::log(microtime() . ' Start cataloginventory_stock_status website_id=0 ', 0, 'product_stock_update.log');
        //website_id=0
        $zcSDStatusModel->saveCatalogInventoryStockStatus($insertA);

        Mage::log(microtime() . ' Start cataloginventory_stock_status website_id=1 ', 0, 'product_stock_update.log');
        //website_id=1
        $zcSDStatusModel->saveCatalogInventoryStockStatus($insertB);



        Mage::log(microtime() . ' Start reindex ', 0, 'product_stock_update.log');
        Mage::getSingleton('index/indexer')
            ->getProcessByCode('cataloginventory_stock');

        Mage::log(microtime() . ' End ', 0, 'product_stock_update.log');
        echo 'Done';
    }

    /**
     * @param $priceBatch
     */
    public static function updatePricesConverter($priceBatch){
        $batchFile = self::CONVERTER_PRICE_UPDATE_LOG;
        $skuS = array_keys($priceBatch);
        $itemsToChange = count($skuS);
        Mage::log(microtime() . " Got items from converter {$itemsToChange}", 0, $batchFile);

        //Get price types
        Mage::log(microtime() . ' Get price types', 0, $batchFile);
        if(empty($priceBatch)){
            Mage::log(microtime() . ' Empty source', 0, $batchFile);
            return;
        }
        if(!empty($priceBatch)){
            $eav = Mage::getSingleton('eav/config');
            $productEt = $eav->getEntityType('catalog_product')->getId();

            //$productAction = Mage::getSingleton('catalog/product_action');

            $priceTypeByStore = array();
            $zcModel = Mage::getModel('zolagocatalog/product');
            $priceType = $zcModel->getConverterPriceType($skuS);
            //reformat by store id
            if(!empty($priceType)){
                foreach ($priceType as $priceTypeData) {
                    $priceTypeByStore[$priceTypeData['sku']][$priceTypeData['store']]
                        = $priceTypeData['price_type'];
                }
            }

            $marginByStore = array();
            $model = Mage::getResourceModel('zolagocatalog/product');
            $priceMarginValues = $model->getPriceMarginValues($skuS);
            //reformat margin
            if (!empty($priceMarginValues)) {
                foreach ($priceMarginValues as $_) {
                    $marginByStore[$_['product_id']][$_['store']] = $_['price_margin'];
                }
                unset($_);
            }

        }
        $insert = array();
        $ids = array();
        $skeleton = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);
        Mage::log(microtime() . ' Start update', 0, $batchFile);
        if (!empty($skeleton)) {
            foreach ($skeleton as $sku => $productId) {
                $stores = array(0,1,2);
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

                    //margin
                    $marginSelected = 0;

                    if (isset($marginByStore[$productId][$storeId])) {
                        $marginSelected = (int)$marginByStore[$productId][$storeId];
                    } else {
                        $marginDefault = isset($marginByStore[$productId][Mage_Core_Model_App::ADMIN_STORE_ID])
                            ? $marginByStore[$productId][Mage_Core_Model_App::ADMIN_STORE_ID] : $marginSelected;
                        $marginSelected = (int)$marginDefault;
                    }

                    $pricesConverter = isset($priceBatch[$sku]) ? (array)$priceBatch[$sku] : false;

                    if ($pricesConverter) {
                        $priceToInsert = isset($pricesConverter[$priceTypeSelected])
                            ? $pricesConverter[$priceTypeSelected] : false;


                        if($priceToInsert){
                            $insert[] = array(
                                'entity_type_id' => $productEt,
                                'attribute_id' => 75,
                                'store_id' => $storeId,
                                'entity_id' => $productId,
                                'value' => Mage::app()->getLocale()->getNumber($priceToInsert + (($priceToInsert * $marginSelected)/100))
                            );
                            //$productIds = array($productId);
                            //$attrData = array('price' => $priceToInsert);

                            //$productAction->updateAttributesNoIndex($productIds, $attrData, $storeId);
                        }
                    }



                }

                $ids[] = $productId;
            }
        }
        Mage::log(microtime() . ' End update', 0, $batchFile);

        if (!empty($insert)) {
            $model->savePriceValues($insert);
            Zolago_Catalog_Helper_Configurable::queue($ids);
        }
    }
}
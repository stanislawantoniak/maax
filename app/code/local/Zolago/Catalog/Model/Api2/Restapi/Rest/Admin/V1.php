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
        return $json;
    }
    protected static function _prepareIsInStock($skus) {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('zolagocataloginventory/stock_website')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->join(array('e' => $resource->getTableName('catalog/product')),
                'e.entity_id = main_table.product_id',
                array())
            ->where('e.sku',array('in',$skus))
            ->columns(
                array(
                    'product_id'=>'main_table.product_id',
                    'website_id'=>'main_table.website_id',
                    'is_in_stock' => 'main_table.is_in_stock',
                    'unique_id' => 'concat(main_table.product_id,"_",main_table.website_id)'
                )
            );
        $collection->setRowIdFieldName('unique_id');
        return $collection;
    }    
    protected static function _getItemCollection($skus) {	
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('zolagopos/stock')->getCollection();
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->join(array('e' => $resource->getTableName('catalog/product')),
                'e.entity_id = main_table.product_id',
                array())
            ->join(array('website' => $resource->getTableName('zolagopos/pos_vendor_website')),
                'website.pos_id = main_table.pos_id',
                array())
            ->join(array('pos' => $resource->getTableName('zolagopos/pos')),
                'pos.pos_id = main_table.pos_id',
                array())
             ->where('e.sku',array('in',$skus))
             ->group('main_table.product_id')
             ->group('website.website_id')
                        
             ->columns(array('qty' => 'sum(main_table.qty)',
                 'product_id' => 'main_table.product_id',
                 'website' => 'website.website_id',
                 'is_active' => 'pos.is_active'));
         return $collection;
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

        $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;


        $tmpStock = array();
        // collect sku
        foreach ($stockBatch as $merchant => $stockData) { 
            $productIds = array_keys($stockData);
            $skuS = array_merge($skuS, $productIds);
        }
        // save old values
        $collection = self::_prepareIsInStock($skuS);
        foreach ($collection as $val) {
            if ($val->getIsInStock()) {
                $tmpStock[$val->getWebsiteId()][$val->getProductId()] = 1;
            }
        }    
        foreach ($stockBatch as $merchant => $stockData) {
            Zolago_Catalog_Helper_Stock::getAvailableStock($stockData, $merchant); // save qty into pos            
        }
        // find changed products
        $isInStock = array();
        $collection = self::_getItemCollection($skuS);
        $productsIdsForSolrAndVarnishBan = array();        
        $productsIds = array();
        foreach ($collection as $val) {            
            $id = $val->getProductId();
            $productsIds[$id] = $id;
            $website = $val->getWebsite();
            $qty = $val->getQty();
            $isActive = $val->getIsActive();
            if (!isset($tmpStock[$website][$id])) {
                // new availability
                if ($qty > 0 && $isActive) {
                    $isInStock[$id][$website] = 1;
                    $productsIdsForSolrAndVarnishBan[$id] = $id;
                    Mage::dispatchEvent("zolagocatalog_converter_stock_save_before", array(
                                   "product_id" => $id,
                               ));
               
                }
            } else {
                if ($qty > 0 && $isActive) {
                    unset($tmpStock[$website][$id]);
                }
            }
        }
        // removed availability
        foreach ($tmpStock as $websiteId => $prodList) {
            foreach ($prodList as $id => $dummy) {
                $productsIdsForSolrAndVarnishBan[$id] = $id;
                $isInStock[$id][$websiteId] = 0;
                Mage::dispatchEvent("zolagocatalog_converter_stock_save_before", array(
                              "product_id" => $id,
                ));
            }
        }

        /*  @var $zcSDItemModel Zolago_CatalogInventory_Model_Resource_Stock_Website */
        $zcSDItemModel = Mage::getResourceModel('zolagocataloginventory/stock_website');
        $zcSDItemModel->saveCatalogInventoryStock($isInStock);
        //reindex
        Mage::getResourceModel('cataloginventory/indexer_stock')
            ->reindexProducts($productsIds);

        // Varnish & Turpentine
        $coll = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productsIdsForSolrAndVarnishBan);

        //send to solr queue & ban url in varnish
        Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $coll));
    }

    /**
     * @param $priceBatch
     */
    public static function updatePricesConverter($priceBatch)
    {
        //queue inform_magento
        $skuS = array_keys($priceBatch);
//        Mage::log('Count: '.count($skuS), 0);
        if (empty($priceBatch)) {
            return;
        }

        $skeleton = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);

        if (empty($skeleton)) {
            return;
        }

        /* @var $model Zolago_Catalog_Model_Resource_Product */
        $model = Mage::getResourceModel('zolagocatalog/product');

        //converter_price_type from configurable products
        $priceType = $model->getConverterPriceTypeConfigurable($skuS);

        //price_margin from configurable products
        $priceMarginValues = $model->getPriceMarginValuesConfigurable($skuS);

        //converter_msrp_type from configurable products
        $priceMSRPSourceManual = $model->getMSRPSourceValuesManualConverterConfigurable($skuS);

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
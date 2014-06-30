<?php

/**
 * Class Zolago_Catalog_AuthController
 */
class Zolago_Catalog_AuthController extends Mage_Core_Controller_Front_Action
{

    public function indexAction(){


        $res
            = '{"ProductPricesUpdate":[{"merchant":"4","data":{"20375-80X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-85E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-99X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-99X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-99X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-65F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-70F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-85E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-85B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-80D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-80E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-80X-80B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-80C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-85E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-80X-70A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70H":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-65G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-80X-85B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-85C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-85D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-70F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-70G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70H":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-70D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1}}}]}';
        $data = json_decode($res);
        $batchFile = 'converter_profilerBatch.log';
        Mage::log(microtime() . ' Start', 0, $batchFile);
        //krumo($data);
        Mage::log(microtime() . ' Get prices array', 0, $batchFile);
        $priceBatch = array();
        if (!empty($data)) {
            foreach ($data as $cmd => $batch) {
                switch ($cmd) {
                    case 'ProductPricesUpdate':
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
                        break;
                    case 'ProductStockUpdate':
                        //
                        break;
                    default:
                       //
                }
            }
            unset($cmd);unset($batch);
        }
        self::updatePricesConverter($priceBatch);

        die('test');

        $data = array(
            'cmd' => 'ProductPricesUpdate',
            'merchant' => 4,
            'data' => array(
                array('price' => 584.63,
                    'price_id' => "marketPrice"
                ),
                array('price' => 575.52,
                    'price_id' => "A"
                )
            ),
            'sku' => "32345-01X-65C"
        );
        Mage::log(microtime() . ' Start', 0, 'converter_profiler.log');

        if (!empty($data)) {
            $productAction = Mage::getSingleton('catalog/product_action');
            $merchant = $data['merchant'];
            $skuV = $data['sku'];

            $sku = $merchant . '-' . $skuV;

            $zcModel = Mage::getModel('zolagocatalog/product');
            $priceType = $zcModel->getConverterPriceTypeBySku($sku);

            $priceTypeSelected = "A";
            if(!empty($priceType) && isset($priceType['price_type'])){
                $priceTypeSelected = $priceType['price_type'];
            }


            Mage::log(microtime() . ' Got system sku', 0, 'converter_profiler.log');
            $productId = Mage::getResourceModel('catalog/product')
                ->getIdBysku($sku);
            Mage::log(microtime() . ' Got product id from sku', 0, 'converter_profiler.log');
            if ($productId) {

                $prices = isset($data['data']) ? $data['data'] : array();
                if(!empty($prices)){

                    $priceA = FALSE;
                    foreach ($prices as $pricesItem) {
                        if ($pricesItem['price_id'] == $priceTypeSelected) {
                            $priceA = $pricesItem['price'];
                        }
                    }

                    Mage::log(microtime() . ' Got priceA from all prices', 0, 'converter_profiler.log');

                    $productIds = array($productId);
                    $attrData = array('price' => $priceA);

                    Mage::log(microtime() . ' Update prices - start', 0, 'converter_profiler.log');
                    $productAction->updateAttributesNoIndex($productIds, $attrData, 0);
                    $productAction->updateAttributesNoIndex($productIds, $attrData, 1);
                    $productAction->updateAttributesNoIndex($productIds, $attrData, 2);
                    Mage::log(microtime() . ' Update prices - end', 0, 'converter_profiler.log');

                    Mage::log(microtime() . ' Add to queue - start', 0, 'converter_profiler.log');
                    Zolago_Catalog_Helper_Configurable::queueProduct($productId);
                    Mage::log(microtime() . ' Add to queue - end', 0, 'converter_profiler.log');
                }


            }
        }

        Mage::log(microtime() . ' Finish', 0, 'converter_profiler.log');
    }

    public static function updatePricesConverter($priceBatch){
        $batchFile = 'converter_profilerBatch.log';
        $skuS = array_keys($priceBatch);

        //Get price types
        Mage::log(microtime() . ' Get price types', 0, $batchFile);
        if(!empty($priceBatch)){
            $eav = Mage::getSingleton('eav/config');
            $productEt = $eav->getEntityType('catalog_product')->getId();

            $productAction = Mage::getSingleton('catalog/product_action');

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
                            $productIds = array($productId);
                            $attrData = array('price' => $priceToInsert);

                            //$productAction->updateAttributesNoIndex($productIds, $attrData, $storeId);
                        }
                    }



                }

                $ids[] = $productId;
            }
        }
        Mage::log(microtime() . ' End update', 0, $batchFile);
        krumo($insert);

        if (!empty($insert)) {
            $model->savePriceValues($insert);
            Zolago_Catalog_Helper_Configurable::queue($ids);
        }
    }
    public function configurableAction()
    {
        Zolago_Catalog_Model_Observer::processConfigurableQueue();
        echo 'Done';
    }

    public function configurableClearAction()
    {
        Zolago_Catalog_Model_Observer::clearConfigurableQueue();
    }


    public static function stockAction()
    {
        $stockId = 1;
        $websiteAdmin = 0;
        $websiteFront = 1;

        $cataloginventoryStockItem = array();
        $cataloginventoryStockStatus0 = array();
        $cataloginventoryStockStatus1 = array();

        /*
         * 1. Test with file
         */
        //Emulate stock data
        $dataXMLJSON= Zolago_Catalog_Helper_Stock::emulateStock();
        //Zend_Debug::dump($dataXMLJSON);

        $dataXML = json_decode($dataXMLJSON);

        $merchant = $dataXML->merchant;
        //calculate available stock
        $stock =(array)$dataXML->data;

        Mage::log(microtime() . ' Start prepare data ', 0, 'product_stock_update.log');
        $data = Zolago_Catalog_Helper_Stock::getAvailableStock($stock,$merchant);


        /*Prepare data to insert*/

        if(empty($data)){
            Mage::log(microtime() . ' Empty source ', 0, 'product_stock_update.log');
            return;
        }


        if(!empty($data)){
            foreach($data as $id => $qty){
                $is_in_stock = ($qty > 0) ? 1 : 0;
                $cataloginventoryStockStatus0 []= "({$id},{$qty},{$is_in_stock},{$stockId},{$websiteAdmin})";
                $cataloginventoryStockStatus1 []= "({$id},{$qty},{$is_in_stock},{$stockId},{$websiteFront})";

                $cataloginventoryStockItem []= "({$id},{$qty},{$is_in_stock},{$stockId})";
            }
        }

        /**
         * 2. Test with all products
         */

        $load = 1;

//        $skuAssoc = Zolago_Catalog_Helper_Data::getIdSkuAssoc();
//
//
//        $qty = 10;
//
//        $i = 0;
//        if(empty($skuAssoc)){
//            Mage::log(microtime() . ' Empty source ', 0, 'product_stock_update.log');
//            return;
//        }
//
//        foreach($skuAssoc as $id => $skuAssocItem){
//            $cataloginventoryStockStatus0 []= "({$id},{$qty},1,{$stockId},{$websiteAdmin})";
//            $cataloginventoryStockStatus1 []= "({$id},{$qty},1,{$stockId},{$websiteFront})";
//
//            $cataloginventoryStockItem []= "({$id},{$qty},1,{$stockId})";
//
//            $i++;
//        }

        $update1 = array_fill(0,$load, implode(',',$cataloginventoryStockItem));
        $updateA = array_fill(0,$load, implode(',',$cataloginventoryStockStatus0));
        $updateB = array_fill(0,$load, implode(',',$cataloginventoryStockStatus1));


        $insert1 = implode(',',$update1);
        $insertA = implode(',',$updateA);
        $insertB = implode(',',$updateB);

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

}




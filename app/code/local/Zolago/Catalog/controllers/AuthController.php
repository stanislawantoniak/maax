<?php

/**
 * Class Zolago_Catalog_AuthController
 */
class Zolago_Catalog_AuthController extends Mage_Core_Controller_Front_Action
{

    public function indexAction(){


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

    public function priceAction(){
        Zolago_Catalog_Model_Observer::processPriceTypeQueue();
    }

}




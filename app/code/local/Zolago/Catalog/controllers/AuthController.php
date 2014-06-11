<?php

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
            Mage::log(microtime() . ' Got system sku', 0, 'converter_profiler.log');
            $productId = Zolago_Catalog_Helper_Data::getSkuAssocId($sku);
            Mage::log(microtime() . ' Got product id from sku', 0, 'converter_profiler.log');
            if ($productId) {

                $prices = isset($data['data']) ? $data['data'] : array();
                if(!empty($prices)){

                    $priceA = FALSE;
                    foreach ($prices as $pricesItem) {
                        if ($pricesItem['price_id'] == "A") {
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

    private function _updateProductQty($database, $product_id, $new_quantity)
    {
        $database->query("UPDATE cataloginventory_stock_item item_stock, cataloginventory_stock_status status_stock

       SET item_stock.qty = '$new_quantity', item_stock.is_in_stock = IF('$new_quantity'>0, 1,0),

       status_stock.qty = '$new_quantity', status_stock.stock_status = IF('$new_quantity'>0, 1,0)

       WHERE item_stock.product_id = '$product_id' AND item_stock.product_id = status_stock.product_id ");

    }

    public static function testAction()
    {

        //Emulate stock data
        $dataXMLJSON= Zolago_Catalog_Helper_Stock::emulateStock();
        Zend_Debug::dump($dataXMLJSON);

        $dataXML = json_decode($dataXMLJSON);




        $merchant = $dataXML->merchant;
        //calculate available stock
        $stock =(array)$dataXML->data;


        $data = Zolago_Catalog_Helper_Stock::getAvailableStock($stock,$merchant);


        /*Prepare data to insert*/
        $cataloginventory_stock_item = array();
        $cataloginventory_stock_status0 = array();
        $cataloginventory_stock_status1 = array();
        //TODO const
        $stockId = 1;
        $websiteAdmin = 0;
        $websiteFront = 1;

        if(!empty($data)){
            foreach($data as $id => $qty){
                $is_in_stock = ($qty > 0) ? 1 : 0;
                $cataloginventory_stock_status0 []= "({$id},{$qty},{$is_in_stock},{$stockId},{$websiteAdmin})";
                $cataloginventory_stock_status1 []= "({$id},{$qty},{$is_in_stock},{$stockId},{$websiteFront})";

                $cataloginventory_stock_item []= "({$id},{$qty},{$is_in_stock},{$stockId})";
            }
        }

        $load = 12500;

//        $skuAssoc = Zolago_Catalog_Helper_Data::getIdSkuAssoc();
//
////
//        $qty = 25;
////
//        $i = 0;
//        foreach($skuAssoc as $id => $skuAssocItem){
//            $cataloginventory_stock_status0 []= "({$id},{$qty},1,1,0)";
//            $cataloginventory_stock_status1 []= "({$id},{$qty},1,1,1)";
//
//            $cataloginventory_stock_item []= "({$id},{$qty},1,1)";
//
//            $i++;
//        }

        $update1 = array_fill(0,$load, implode(',',$cataloginventory_stock_item));
        $updateA = array_fill(0,$load, implode(',',$cataloginventory_stock_status0));
        $updateB = array_fill(0,$load, implode(',',$cataloginventory_stock_status1));


        $insert1 = implode(',',$update1);
        $insertA = implode(',',$updateA);
        $insertB = implode(',',$updateB);


        $zcSDModel = Mage::getResourceModel('zolagocatalog/stock_data');

        Mage::log(microtime() . ' Start cataloginventory_stock_item ', 0, 'product_stock_update.log');
        $zcSDModel->saveCatalogInventoryStockItem($insert1);

        Mage::log(microtime() . ' Start cataloginventory_stock_status website_id=0 ', 0, 'product_stock_update.log');
        //website_id=0
        $zcSDModel->saveCatalogInventoryStockStatus($insertA);

        Mage::log(microtime() . ' Start cataloginventory_stock_status website_id=1 ', 0, 'product_stock_update.log');
        //website_id=1
        $zcSDModel->saveCatalogInventoryStockStatus($insertB);



        Mage::log(microtime() . ' Start reindex ', 0, 'product_stock_update.log');
        Mage::getSingleton('index/indexer')
            ->getProcessByCode('cataloginventory_stock');

        Mage::log(microtime() . ' End ', 0, 'product_stock_update.log');
        echo 'Done';
    }

}




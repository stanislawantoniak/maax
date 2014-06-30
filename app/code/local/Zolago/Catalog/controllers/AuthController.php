<?php

/**
 * Class Zolago_Catalog_AuthController
 */
class Zolago_Catalog_AuthController extends Mage_Core_Controller_Front_Action
{

    public function indexAction(){
        $apiModel = new Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1();


        //$res = '{"ProductPricesUpdate":[{"merchant":"4","data":{"20375-80X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-85E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-99X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-99X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-99X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-65F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-70F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-65G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-85E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-85B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-75F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-80D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-80E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-75B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-80X-80B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-80C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-00X-85E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-00X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-80X-70A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-75B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70H":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-65G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-70E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-80X-85B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-85C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-80X-85D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-70F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-80B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-70G":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70H":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70E":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70F":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20375-80X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20375-80X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-99X-70D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70A":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-99X-70B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1}}}]}';

        $res = '{"ProductStockUpdate":[{"merchant":"4","data":{"32345-01X-65F":{"K88":48,"K99":4,"K01":8},"32345-01X-75A":{"K88":48,"K99":4,"K01":8},"32345-01X-65E":{"K88":48,"K99":4,"K01":8},"32345-01X-75C":{"K88":48,"K99":4,"K01":8},"32345-01X-75B":{"K88":48,"K99":4,"K01":8},"32345-01X-75D":{"K88":48,"K99":4,"K01":8},"32345-01X-65D":{"K88":48,"K99":4,"K01":8},"32345-01X-65C":{"K88":48,"K99":4,"K01":8},"32345-01X-70B":{"K88":48,"K99":4,"K01":8},"32345-01X-70C":{"K88":48,"K99":4,"K01":8},"32345-01X-70D":{"K88":48,"K99":4,"K01":8},"32345-01X-70E":{"K88":48,"K99":4,"K01":8},"32345-01X-80C":{"K88":48,"K99":4,"K01":8},"32345-01X-80B":{"K88":48,"K99":4,"K01":8}}}]}';
        $data = json_decode($res);
        $apiModel->api2($data);
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




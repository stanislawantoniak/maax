<?php

/**
 * Class Zolago_Catalog_AuthController
 */
class Zolago_Catalog_AuthController extends Mage_Core_Controller_Front_Action
{

    public function indexAction(){
        $apiModel = new Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1();


        $res = '{"ProductPricesUpdate":[{"merchant":"4","data":{"19244-99X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18800-99X-XL":{"marketPrice":125.31,"A":123.36,"B":24.5,"C":23.1,"salePriceBefore":125.31,"Z":23.1},"19244-99X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-70F":{"marketPrice":277.56,"A":273.24,"B":24.5,"C":23.1,"salePriceBefore":277.56,"Z":23.1},"19244-00X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70H":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18724-90X-XXL":{"marketPrice":131.65,"A":129.6,"B":24.5,"C":23.1,"salePriceBefore":131.65,"Z":23.1},"19244-80X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18728-59X-L":{"marketPrice":98.98,"A":97.44,"B":24.5,"C":23.1,"salePriceBefore":98.98,"Z":23.1},"19244-00X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-85E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-80B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-80C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-80D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18724-59X-XXL":{"marketPrice":131.65,"A":129.6,"B":201,"C":301,"salePriceBefore":131.65,"Z":401},"19244-80X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-80E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18732-59X-L":{"marketPrice":166.75,"A":164.16,"B":24.5,"C":23.1,"salePriceBefore":166.75,"Z":23.1},"19244-80X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-65F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18731-59X-XL":{"marketPrice":182.36,"A":179.52,"B":24.5,"C":23.1,"salePriceBefore":182.36,"Z":23.1},"18731-90X-XL":{"marketPrice":182.36,"A":179.52,"B":24.5,"C":23.1,"salePriceBefore":182.36,"Z":23.1},"19244-80X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18732-59X-M":{"marketPrice":166.75,"A":164.16,"B":24.5,"C":23.1,"salePriceBefore":166.75,"Z":23.1},"19244-80X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-65B":{"marketPrice":185.04,"A":192.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-80X-75B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70H":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-70A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"20183-00X-65B":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"19244-00X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-75B":{"marketPrice":462.61,"A":455.4,"B":24.5,"C":23.1,"salePriceBefore":462.61,"Z":23.1},"19244-00X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18732-90X-XL":{"marketPrice":166.75,"A":164.16,"B":24.5,"C":23.1,"salePriceBefore":166.75,"Z":23.1},"19244-00X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-65F":{"marketPrice":277.56,"A":273.24,"B":24.5,"C":23.1,"salePriceBefore":277.56,"Z":23.1},"19244-99X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-65E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-75A":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-65G":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-65F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18800-99X-L":{"marketPrice":125.31,"A":123.36,"B":24.5,"C":23.1,"salePriceBefore":125.31,"Z":23.1},"19244-99X-65C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-65D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-00X-75F":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-75E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18800-99X-M":{"marketPrice":125.31,"A":123.36,"B":24.5,"C":23.1,"salePriceBefore":125.31,"Z":23.1},"19244-99X-65B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-75D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-75C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-75B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18731-59X-M":{"marketPrice":91.18,"A":89.76,"B":24.5,"C":23.1,"salePriceBefore":91.18,"Z":23.1},"20183-00X-65D":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"20183-00X-65C":{"marketPrice":194.79,"A":191.76,"B":24.5,"C":23.1,"salePriceBefore":194.79,"Z":23.1},"18800-99X-S":{"marketPrice":125.31,"A":123.36,"B":24.5,"C":23.1,"salePriceBefore":125.31,"Z":23.1},"19244-99X-85C":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18731-00X-MXR":{"marketPrice":182.36,"A":179.52,"B":24.5,"C":23.1,"salePriceBefore":182.36,"Z":23.1},"19244-99X-85D":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-85B":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"19244-99X-85E":{"marketPrice":185.04,"A":182.16,"B":24.5,"C":23.1,"salePriceBefore":185.04,"Z":23.1},"18800-99X-XXL":{"marketPrice":125.31,"A":123.36,"B":24.5,"C":23.1,"salePriceBefore":125.31,"Z":23.1},"18731-90X-M":{"marketPrice":182.36,"A":179.52,"B":24.5,"C":23.1,"salePriceBefore":182.36,"Z":23.1}}}]}';

        //$res = '{"ProductStockUpdate":[{"merchant":"4","data":{"32345-01X-65F":{"K88":48,"K99":4,"K01":8},"32345-01X-75A":{"K88":48,"K99":4,"K01":8},"32345-01X-65E":{"K88":48,"K99":4,"K01":8},"32345-01X-75C":{"K88":48,"K99":4,"K01":8},"32345-01X-75B":{"K88":48,"K99":4,"K01":8},"32345-01X-75D":{"K88":48,"K99":4,"K01":8},"32345-01X-65D":{"K88":48,"K99":4,"K01":8},"32345-01X-65C":{"K88":48,"K99":4,"K01":8},"32345-01X-70B":{"K88":48,"K99":4,"K01":8},"32345-01X-70C":{"K88":48,"K99":4,"K01":8},"32345-01X-70D":{"K88":48,"K99":4,"K01":8},"32345-01X-70E":{"K88":48,"K99":4,"K01":8},"32345-01X-80C":{"K88":48,"K99":4,"K01":8},"32345-01X-80B":{"K88":48,"K99":4,"K01":8}}}]}';
        $data = json_decode($res);
        $apiModel->api2($data);
    }

    public function configurableAction()
    {
        Zolago_Catalog_Model_Observer::processConfigurableQueue();
    }

    public function configurableClearAction()
    {
        Zolago_Catalog_Model_Observer::clearConfigurableQueue();
    }


    public function testAction(){

        echo date("Y-m-d H:i:s") . " " .microtime();
        $merchant = 5;
        $skuS = array('5-14644-CZARNY','5-13428-CZARNY','4-32448-99X-L');



        $model = Mage::getResourceModel('zolagocatalog/product');
//
        $productIds = $model->getSkuIdAssoc($skuS);
//
//        $priceType = $model->getProductsPriceData($merchant,$productIds);
//        Zend_Debug::dump($priceType);

        $collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
//        $collection->addAttributeToFilter('sku', array('in'=>$skuS));
        $collection->addIdFilter($productIds);
        $collection->addAttributeToSelect(array(
            "price",
            "converter_price_type",
            "price_margin",
            "converter_msrp_type",
            'store_id',
            'udropship_vendor'
        ));

        foreach ($collection as $collectionItem) {

            Zend_Debug::dump($collectionItem->getData());
        }


//        $collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
//        //Zend_Debug::dump($collection->getDetails());
//
//        // Vaild collection
//        $collection->addAttributeToSelect(array("price_margin", "converter_price_type"));
//        //$collection->addAttributeToFilter('type_id', 'simple');
//        $collection->addAttributeToFilter('udropship_vendor', 5);
//
//        //$collection->addIdFilter(array(25812));
//
//        foreach ($collection as $collectionItem) {
//            $id = $collectionItem->getData("entity_id");
//            $price_margin = $collectionItem->getData("price_margin");
//            $converter_price_type = $collectionItem->getData("converter_price_type");
//
//            //Zend_Debug::dump($collectionItem->getData());
//            if(!empty($price_margin) || !empty($converter_price_type)){
//                Zend_Debug::dump($id,$price_margin, $converter_price_type,"-----------------");
//            }
//
//        }
        echo date("Y-m-d H:i:s") . " " .microtime();
    }


}




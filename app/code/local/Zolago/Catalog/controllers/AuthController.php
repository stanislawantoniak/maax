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
            krumo($sku);
            $zcModel = Mage::getModel('zolagocatalog/product');
            $priceType = $zcModel->getConverterPriceTypeBySku($sku);
            krumo($priceType);
            die('test');
            Mage::log(microtime() . ' Got system sku', 0, 'converter_profiler.log');
            $productId = Mage::getResourceModel('catalog/product')
                ->getIdBysku($sku);
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


}




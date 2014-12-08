<?php
/**
 * Class Zolago_Catalog_Helper_Stock
 */
class Zolago_Catalog_Helper_Stock extends Mage_Core_Helper_Abstract
{
    /**
     * @param bool $testMode
     *
     * @return string
     */
    public static function emulateStock($testMode = false)
    {

        /*Load xml data*/
        $base_path = Mage::getBaseDir('base');
        // $file = $base_path . '/var/log/stock.xml';
        $file = $base_path . '/var/log/stock2.xml';

        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

        $merchant = (int)$xml->merchant;

        $dataXML = array();
        foreach ($xml->stocksPerPOS->pos as $pos) {
            $posId = (string)$pos->attributes()['id'];

            foreach ($pos->product as $product) {
                $skuMerchant = (string)$product->sku;
                $stock = (int)$product->stock;

                $sku = $merchant . "-" . $skuMerchant;
                $dataXML[$sku][$posId] = $stock;
            }
        }

        $res = array('merchant' => $merchant, 'data' => $dataXML);

        return json_encode($res);

    }

    /**
     * @param $dataStock
     * @param $merchant
     *
     * @return array
     */
    public static function getAvailableStock($dataStock, $merchant)
    {
        //$batchFile = Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1::CONVERTER_STOCK_UPDATE_LOG;

        if (empty($dataStock)) {
            return array();
        }
        $data = array();

        /**
         * Prepare data
         *
         * 1 calculate available stock
         * 2 calculate stock on open orders
         * 3 stock = available stock - stock on open orders

         */
        //1. get min POS stock (calculate available stock)
        $posResourceModel = Mage::getResourceModel('zolagopos/pos');
        $minPOSValues = $posResourceModel->getMinPOSStock();

        //2. calculate stock on open orders
        $zcSDModel = Mage::getResourceModel('zolagopos/pos');
        $openOrdersQty = $zcSDModel->calculateStockOpenOrders($merchant);

        //-------Prepare data

        //if (!empty($minPOSValues)) {
            foreach ($dataStock as $sku => $dataStockItem) {
                $dataStockItems = (array)$dataStockItem;
                if (!empty($dataStockItems)) {
                    foreach ($dataStockItems as $stockId => $posStockConverter) {
                        //false if POS is not active
                        $minimalStockPOS = isset($minPOSValues[$stockId]) ? (int)$minPOSValues[$stockId] : 0;
                        //if ($minimalStockPOS) {
                            $openOrderQty = isset($openOrdersQty[$sku]) ? (int)$openOrdersQty[$sku]['qty'] : 0;

                            //available stock = if [POS stock from converter]>[minimal stock from POS] then [POS stock from converter] - [minimal stock from POS] else 0
                            $data[$sku][$stockId] = ($posStockConverter > $minimalStockPOS)
                                ? ($posStockConverter - $minimalStockPOS - $openOrderQty) : 0;

                            //Mage::log(microtime() . "{$sku}: {$stockId} - POS stock from converter {$posStockConverter}, minimal stock from POS {$minimalStockPOS}, Open orders stock {$openOrderQty} ", 0, $batchFile);
                        //}

                    }
                    unset($posStockConverter);
                }
            }
            unset($dataStockItem);
        //}

        $skus = array_keys($data);
        $skuIdAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc($skus);

        $dataSum = array();
        foreach ($data as $sku => $_) {
            if (isset($skuIdAssoc[$sku])) {
                $qty = array_sum((array)$_);
                $dataSum[$skuIdAssoc[$sku]] = $qty;
                //Mage::log(microtime() . " {$sku} Stock qty sum {$qty}", 0, $batchFile);
            }
        }
        unset($_);


        return $dataSum;
    }
}
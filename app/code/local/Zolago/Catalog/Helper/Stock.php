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
        echo $merchant;

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
        $posResourceModel = Mage::getResourceModel('zolagopos/pos_collection');
        $minPOSValues = $posResourceModel->getMinPOSStock();

        //2. calculate stock on open orders
        $zcSDModel = Mage::getResourceModel('zolagocatalog/stock_data');
        $openOrdersQty = $zcSDModel->calculateStockOpenOrders($merchant);

        //-------Prepare data

        if (!empty($minPOSValues)) {
            foreach ($dataStock as $sku => $dataStockItem) {
                $dataStockItems = (array)$dataStockItem;
                if (!empty($dataStockItems)) {
                    foreach ($dataStockItems as $stockId => $posStockConverter) {
                        $minimalStockPOS = $minPOSValues[$stockId];
                        $openOrderQty = isset($openOrdersQty[$sku]) ? $openOrdersQty[$sku]['qty'] : 0;
                        //available stock = if [POS stock from converter]>[minimal stock from POS] then [POS stock from converter] - [minimal stock from POS] else 0
                        $data[$sku][$stockId] = ($posStockConverter > $minimalStockPOS) ? ($posStockConverter
                            - $minimalStockPOS - $openOrderQty) : 0;
                    }
                    unset($posStockConverter);
                }
            }
            unset($dataStockItem);
        }
        $skus = array_keys($data);
        $skuIdAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc($skus);

        $dataSum = array();
        foreach ($data as $sku => $_) {
            if (isset($skuIdAssoc[$sku])) {
                $dataSum[$skuIdAssoc[$sku]] = array_sum((array)$_);
            }
        }
        unset($_);


        return $dataSum;
    }
}
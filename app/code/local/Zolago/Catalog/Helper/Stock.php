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
            $l = $pos->attributes();
            $posId = (string)$l['id'];

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
     * @param $vendor
     * @return array
     */
    public static function getAvailableStock($dataStock, $vendor)
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
        $skuS = array_keys($dataStock);
        //1. get min POS stock (calculate available stock)
        $posResourceModel = Mage::getResourceModel('zolagopos/pos');
        $minPOSValues = $posResourceModel->getMinPOSStock($vendor);
        $availablePos = array_keys($minPOSValues);

        //-------Prepare data
        foreach ($dataStock as $sku => $dataStockItem) {
            $dataStockItems = (array)$dataStockItem;
            if (!empty($dataStockItems)) {
                foreach ($dataStockItems as $stockId => $posStockConverter) {

                    //false if POS is not active
                    if (in_array($stockId, $availablePos)) {
                        $minimalStockPOS = isset($minPOSValues[$stockId]) ? (int)$minPOSValues[$stockId] : 0;

                        //available stock = if [POS stock from converter]>[minimal stock from POS] then [POS stock from converter] - [minimal stock from POS] else 0
                        $data[$sku][$stockId] = ($posStockConverter > $minimalStockPOS)
                            ? ($posStockConverter - $minimalStockPOS) : 0;
                    }


                }
                unset($posStockConverter);
            }
        }
        unset($dataStockItem);

        $dataSum = array();
        foreach ($data as $sku => $_) {
                $qty = array_sum((array)$_);
                $dataSum[$sku] = $qty;
        }
        unset($_);


        return $dataSum;
    }
}
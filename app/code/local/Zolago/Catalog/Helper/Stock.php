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
     * @param $vendorId
     * @return array
     */
    public static function getAvailableStock($dataStock, $vendorId)
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
        $minPOSValues = $posResourceModel->getMinPOSStock($vendorId);
        $availablePos = array_keys($minPOSValues);

        $skuIdAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc($skuS);

        //2. calculate stock on open orders (reservation)

        /* @var $zcSDModel  Zolago_Pos_Model_Resource_Pos */
        $zcSDModel = Mage::getResourceModel('zolagopos/pos');
        $openOrdersQty = $zcSDModel->calculateStockOpenOrders($vendorId, $skuS); //reservation


        $dataAllocateToPOS = array();

        //-------Prepare data
        foreach ($dataStock as $sku => $dataStockItem) {
            $dataStockItems = (array)$dataStockItem;
            if (!empty($dataStockItems)) {
                foreach ($dataStockItems as $stockId => $posStockConverter) {
                    if (!in_array($stockId, $availablePos)) //no POS or it is not active
                        continue;

                    $minimalStockPOS = isset($minPOSValues[$stockId]) ? (int)$minPOSValues[$stockId] : 0;
                    $reservedOnPOStock = isset($openOrdersQty[$sku][$stockId]) ? (int)$openOrdersQty[$sku][$stockId] : 0;

                    $data[$sku][$stockId] = $posStockConverter - $minimalStockPOS - $reservedOnPOStock;

                    //Allocate Product STOCK by POS
                    $dataAllocateToPOS[$skuIdAssoc[$sku]][$stockId] = $data[$sku][$stockId];


                }
                unset($posStockConverter);
            }
        }
        unset($dataStockItem);


        //Calculate sum
        $dataSum = array();
        foreach ($data as $sku => $_) {
            $qty = array_sum((array)$_);
            $dataSum[$skuIdAssoc[$sku]] = $qty;
        }
        unset($_);
        self::allocateProductByPOS($vendorId,$dataAllocateToPOS);


        return $dataSum;
    }

    /**
     * @return Zolago_Pos_Model_Resource_Pos_Collection
     */
    public function getVendorPOSes($vendorId) {
        /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
        $collection = Mage::getResourceModel("zolagopos/pos_collection");
        $collection->addVendorFilter((int)$vendorId);
        $collection->addActiveFilter();
        $collection->setOrder("priority", Varien_Data_Collection::SORT_ORDER_DESC);
        return $collection;
    }

    /**
     * @param $vendorId
     * @param $dataAllocateToPOS
     */
    public static function allocateProductByPOS($vendorId, $dataAllocateToPOS)
    {
        $poses = self::getVendorPOSes($vendorId);
        $posIds = array();
        foreach ($poses as $pos) {
            /* @var $pos Zolago_Pos_Model_Pos */
            $posIds[$pos->getExternalId()] = $pos->getId();
        }

        if (empty($posIds))
            return;

        $rows = array();

        $table = Mage::getModel("zolagopos/stock")->getResource()->getMainTable();
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($dataAllocateToPOS as $productId => $data) {
            foreach ($data as $stockExternalId => $qty) {
                $posId = isset($posIds[$stockExternalId]) ? $posIds[$stockExternalId] : FALSE;
                if ($posId) {
                    $adapter->insertOnDuplicate(
                        $table,
                        array("product_id" => (int)$productId, "pos_id" => (int)$posId, "qty" => $qty),
                        array("product_id", 'pos_id', 'qty')
                    );
                }

            }
        }

        if (empty($rows))
            return;

    }
}
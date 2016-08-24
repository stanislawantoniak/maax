<?php

/**
 * Import product prices
 */
class ZolagoOs_Import_Model_Import_Price
    extends ZolagoOs_Import_Model_Import
{
    protected $_vendor;

    /**
     * ZolagoOs_Import_Model_Import_Price constructor.
     */
    public function __construct()
    {
        $this->_vendor = $this->getExternalId();
    }


    /**
     * Implement _getImportEntityType() method.
     */
    protected function _getImportEntityType()
    {
        return "price";
    }

    protected function _getFileExtension()
    {
        return "csv";
    }


    /**
     * File name for _getPath()
     *
     * @return string
     */
    public function _getFileName()
    {
        return $this->getHelper()->getPriceFile();

    }

    protected function _import()
    {
        $vendorId = $this->getExternalId();
        $fileName = $this->_getPath();

        try {

            $priceBatch = [];
            $invalidPriceScan = [];
            $row = 1;
            if (($fileContent = fopen($fileName, "r")) !== FALSE) {
                while (($data = fgetcsv($fileContent, 100000000, ";")) !== FALSE) {

                    if ($row > 1) {
//                        $sku = $vendorId . "-" . $data[0];
                        $sku = $data[0];
                        if ((float)$data[1] > 0) {
                            $priceBatch[$sku]["A"] = (float)$data[1];
                        } else {
                            $invalidPriceScan[$sku][] = "{$sku} invalid price type A: " . $data[1];
                        }
                        if ((float)$data[2] > 0) {
                            $priceBatch[$sku]["B"] = (float)$data[2];
                        } else {
                            $invalidPriceScan[] = "{$sku} invalid price type B: " . $data[2];
                        }
                        if ((float)$data[3] > 0) {
                            $priceBatch[$sku]["C"] = (float)$data[3];
                        } else {
                            $invalidPriceScan[] = "{$sku} invalid price type C: " . $data[3];
                        }
                        if ((float)$data[4] > 0) {
                            $priceBatch[$sku]["Z"] = (float)$data[4];
                        } else {
                            $invalidPriceScan[] = "{$sku} invalid price type Z: " . $data[4];
                        }
                        if ((float)$data[5] > 0) {
                            $priceBatch[$sku]["salePriceBefore"] = (float)$data[5];
                        } else {
                            $invalidPriceScan[] = "{$sku} invalid price type salePriceBefore: " . $data[5];
                        }
                    }
                    $row++;
                    unset($sku);
                }
                fclose($fileContent);
            }

            if (empty($priceBatch)) {
                $this->log("NO VALID DATA FOUND IN THE FILE", Zend_Log::ERR);
                return $this;
            }


            //1. validate SKU(S) not found in the system
            $notValidSkus = $this->getHelper()->getNotValidSkus($priceBatch, $vendorId);
            if (!empty($notValidSkus)) {
                $notValidSkusLine = implode(", ", array_keys($notValidSkus));
                $notValidSkusCount = count($notValidSkus);
                $this->log("FILE CONTAINS {$notValidSkusCount} INVALID SKU(S): {$notValidSkusLine}", Zend_Log::ERR);
                // Remove invalid skus from batch
                foreach ($notValidSkus as $sku => $msg) {
                    unset($priceBatch[$sku]);
                }
            }
            //2. validate SKU(S) with invalid prices <=0)
            if(!empty($invalidPriceScan)){
                $this->log("INVALID PRICE ANALYSIS RESULT: " . implode(" ;", $invalidPriceScan), Zend_Log::ERR);
            }
            //--validate

            /** @var Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 $restApi */
            $restApi = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');

            $numberQ = 20;
            if (count($priceBatch) > $numberQ) {
                $priceBatchC = array_chunk($priceBatch, $numberQ, true);

                foreach ($priceBatchC as $priceBatchCItem) {
                    $restApi::updatePricesConverter($priceBatchCItem);
                }
                unset($priceBatchCItem);
            } else {
                $restApi::updatePricesConverter($priceBatch);
            }
            $priceBatchCount = count($priceBatch);
            $this->log("SKU(S) SENT TO PROCESS: {$priceBatchCount}", Zend_Log::INFO);
            
            $this->_moveProcessedFile();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}

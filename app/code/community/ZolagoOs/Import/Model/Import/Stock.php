<?php

/**
 * Import product stock
 */
class ZolagoOs_Import_Model_Import_Stock
    extends ZolagoOs_Import_Model_Import
{

    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ';';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * Implement _getImportEntityType() method.
     */
    protected function _getImportEntityType()
    {
        return "stock";
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
        return $this->getHelper()->getStockFile();

    }

    protected function _import()
    {

        $vendorId = $this->getExternalId();

        $fileName = $this->_getPath();

        //Get first active POS
        $pos = Mage::getModel("zolagopos/pos")
            ->getCollection()
            ->addActiveFilter()
            ->getFirstItem();
        $posId = $pos->getId();

        if (!$posId) {
            $this->log("ACTIVE POS NOT FOUND", Zend_Log::ERR);
            return $this;
        }

        $posExternalId = $pos->getExternalId();

        if (!$posExternalId) {
            $this->log("POS EXTERNAL ID CAN NOT BE UNDEFINED (POS_ID: {$posId})", Zend_Log::ERR);
            return $this;
        }

        try {

            $stockBatch = [];
            $duplicateSkuScan = [];
            $wrongLineFormatScan = [];
            $row = 0;
            if (($fileContent = fopen($fileName, "r")) !== FALSE) {
                while (($data = fgetcsv($fileContent, null, $this->_delimiter, $this->_enclosure)) !== FALSE) {
                    $row++;
                    
                    if ($row == 1) {
                        //skip header
                        continue;
                    }

                    //$sku = $vendorId . "-" . $data[0];
                    $sku = $data[0];

                    if(isset($stockBatch[$vendorId][$sku])){
                        $duplicateSkuScan[$row] = "LINE#{$row}: SKU {$sku}";
                    }

                    $stockData = array_slice($data, 1, count($data)-1, true);
                    if (count($data) !== 2 ||
                        $this->getHelper()->isContainNonNumericValues($stockData) ||
                        0 // $this->getHelper()->isContainNegativeValues($stockData)
                    ) {
                        $wrongLineFormatScan[$row] = "LINE#{$row} (SKU: {$sku})";
                        continue;
                    }
                    //$sku = $vendorId . "-" . $data[0];

                    $stockBatch[$vendorId][$sku] = array(
                        $posExternalId => $data[1]
                    );


                }
                fclose($fileContent);
            }


            if (empty($stockBatch)) {
                $this->log("NO VALID DATA FOUND IN THE FILE", Zend_Log::ERR);
                return $this;
            }

            //1. validate SKU(S) not found in the system
            $notValidSkus = $this->getHelper()->getNotValidSkus($stockBatch[$vendorId], $vendorId);

            if (!empty($notValidSkus)) {
                $notValidSkusLine = implode(", ", array_keys($notValidSkus));
                $notValidSkusCount = count($notValidSkus);
                $this->log("FILE CONTAINS {$notValidSkusCount} INVALID SKU(S): {$notValidSkusLine}", Zend_Log::ERR);
                // Remove invalid skus from batch
                foreach ($notValidSkus as $sku => $msg) {
                    unset($stockBatch[$vendorId][$sku]);
                }
            }

            if (empty($stockBatch)) {
                $this->log("NO VALID DATA FOUND IN THE FILE", Zend_Log::ERR);
                return $this;
            }

            //2. validate wrong line format
            if (!empty($wrongLineFormatScan)) {
                $wrongLineFormatScanCount = count($wrongLineFormatScan);
                $this->log("INVALID LINE FORMAT ANALYSIS RESULT: Wrong lines - {$wrongLineFormatScanCount}: " . implode(" ;", $wrongLineFormatScan), Zend_Log::ERR);
            }
            //3. validate duplicated SKU(S)
            if (!empty($duplicateSkuScan)) {
                $this->log("DUPLICATED SKU(s) ANALYSIS RESULT: " . implode(" ;", $duplicateSkuScan), Zend_Log::ERR);
            }
            //--validate

            /** @var Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 $restApi */
            $restApi = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
            if (!empty($stockBatch)) {
                $numberQ = 20;
                if (count($stockBatch) > $numberQ) {
                    $stockBatchC = array_chunk($stockBatch, $numberQ, true);
                    foreach ($stockBatchC as $stockBatchCItem) {
                        $restApi::updateStockConverter($stockBatchCItem);
                    }
                    unset($stockBatchCItem);
                } else {
                    $restApi::updateStockConverter($stockBatch);
                }
            }
            $stockBatchCount = count($stockBatch[$vendorId]);
            
            $this->log("SKU(S) SENT TO PROCESS: {$stockBatchCount}", Zend_Log::INFO);

            $this->_moveProcessedFile();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}

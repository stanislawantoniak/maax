<?php

/**
 * Import product prices
 */
class ZolagoOs_Import_Model_Import_Price
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

    function tofloat($num)
    {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $num));
        }

        return floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep + 1, strlen($num)))
        );
    }

    protected function _import()
    {
        $vendorId = $this->getExternalId();
        $fileName = $this->_getPath();

        try {

            $priceBatch = [];
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


                    $pricesData = array_slice($data, 1, count($data)-1, true);

                    if (isset($priceBatch[$sku])) {
                        $duplicateSkuScan[$row] = "LINE#{$row}: SKU {$sku}";
                    }
                    if (count($data) !== 6 ||
                        $this->getHelper()->isContainNonNumericValues($pricesData) ||
                        $this->getHelper()->isContainNegativeValues($pricesData)
                    ) {
                        $wrongLineFormatScan[$row] = "LINE#{$row} (SKU: {$sku})";
                        continue;
                    }

                    if ((float)$data[1] >= 0) {
                        $priceBatch[$sku]["A"] = (float)$data[1];
                    }
                    if ((float)$data[2] >= 0) {
                        $priceBatch[$sku]["B"] = (float)$data[2];
                    }
                    if ((float)$data[3] >= 0) {
                        $priceBatch[$sku]["C"] = (float)$data[3];
                    }
                    if ((float)$data[4] >= 0) {
                        $priceBatch[$sku]["Z"] = (float)$data[4];
                    }
                    if ((float)$data[5] >= 0) {
                        $priceBatch[$sku]["salePriceBefore"] = (float)$data[5];
                    }
                    unset($sku, $pricesData);

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


            if (empty($priceBatch)) {
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

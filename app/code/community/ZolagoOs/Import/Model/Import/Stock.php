<?php

/**
 * Import product stock
 */
class ZolagoOs_Import_Model_Import_Stock
    extends ZolagoOs_Import_Model_Import
{

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
            $this->log("POS EXTERNAL ID CAN NOT BE UNDEFINED", Zend_Log::ERR);
            return $this;
        }

        try {

            $stockBatch = [];
            $row = 1;
            if (($fileContent = fopen($fileName, "r")) !== FALSE) {
                while (($data = fgetcsv($fileContent, 100000, ";")) !== FALSE) {
                    if ($row > 1) {
                        $stockBatch[$vendorId][$vendorId . "-" . $data[0]] = array(
                            $posExternalId => $data[1]
                        );
                    }
                    $row++;

                }
                fclose($fileContent);
            }

            /** @var Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 $restApi */
            $restApi = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
            if (!empty($stockBatch)) {
                $numberQ = 20;
                if (count($stockBatch) > $numberQ) {
                    $stockBatchC = array_chunk($stockBatch, $numberQ);
                    foreach ($stockBatchC as $stockBatchCItem) {
                        $restApi::updateStockConverter($stockBatchCItem);

                    }
                    unset($stockBatchCItem);
                } else {
                    $restApi::updateStockConverter($stockBatch);
                }
            }
            //$this->_moveProcessedFile();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}

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
        $vendorId = $this->getVendorId();
        $fileName = $this->_getPath();

        try {

            $priceBatch = [];
            $row = 1;
            if (($fileContent = fopen($fileName, "r")) !== FALSE) {
                while (($data = fgetcsv($fileContent, 100000, ";")) !== FALSE) {

                    if ($row > 1) {
                        $priceBatch[$vendorId . "-" . $data[0]] = array(
                            "A" => $data[1],
                            "B" => $data[2],
                            "C" => $data[3],
                            "Z" => $data[4],
                            "salePriceBefore" => $data[5]
                        );
                    }
                    $row++;

                }
                fclose($fileContent);
            }


            /** @var Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 $restApi */
            $restApi = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
            if (!empty($priceBatch)) {
                $numberQ = 20;
                if (count($priceBatch) > $numberQ) {
                    $priceBatchC = array_chunk($priceBatch, $numberQ);
                    foreach ($priceBatchC as $priceBatchCItem) {
                        $restApi::updatePricesConverter($priceBatchCItem);

                    }
                    unset($priceBatchCItem);
                } else {
                    $restApi::updatePricesConverter($priceBatch);
                }
            }
            //$this->_moveProcessedFile();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * @return array
     */
    public function getVendorId()
    {
        return $this->_vendor;
    }
}

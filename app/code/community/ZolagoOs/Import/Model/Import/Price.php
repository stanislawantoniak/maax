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
        krumo($vendorId);

        if (empty($vendorId)) {
            $this->log("CONFIGURATION ERROR: EMPTY VENDOR ID", Zend_Log::ERR);
            return $this;
        }

        //1. Read file
        $fileName = $this->_getPath();

        if (empty($fileName)) {
            $this->log("CONFIGURATION ERROR: EMPTY PRODUCT IMPORT FILE", Zend_Log::ERR);
            return $this;
        }

        if (!file_exists($fileName)) {
            $this->log("CONFIGURATION ERROR: IMPORT FILE {$fileName} NOT FOUND", Zend_Log::ERR);
            return $this;
        }
        try {

           $lines = [];


            if (($fileContent = fopen ($fileName,"r")) !== FALSE) {
                while (($data = fgetcsv($fileContent)) !== FALSE)  {

                    $num = count($data);


                    
                    for ($c=1; $c < $num; $c++) {
                        //echo $data[$c] . "<br />\n";
                    }
                }
                fclose ($fileContent);
            }


        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     *
     */
    protected function _moveProcessedFile()
    {
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $date = date('Y_m_d_H_i_s', $currentTimestamp);

        $fileName = $this->_getPath();

        $path = $this->getHelper()->getProcessedFilePlace()
            . DS . $this->getVendorId()
            . DS . $this->_getImportEntityType();

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $newfile = $path . DS . $date . ".csv";


        if (!copy($fileName, $newfile)) {
            $this->log("Can not move file to processed directory", 2);
        } else {
            unlink($fileName);
        }
    }

    /**
     * @return array
     */
    public function getVendorId()
    {
        return $this->_vendor;
    }


    public function runImport()
    {
        $this->_import();
    }

}

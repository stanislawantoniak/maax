<?php

/**
 * parent class for Integrator
 */
abstract class ZolagoOs_OrdersExport_Model_Export_Abstract
    extends Varien_Object
{
   
    protected $_helper;


    public function getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('zosordersexport');
        }
        return $this->_helper;
    }

    public function getExternalId()
    {
        if (!$this->_externalId) {
            $this->_externalId = $this->getHelper()->getExternalId();
        }
        return $this->_externalId;
    }


    public function pushLinesToFile($fileName, $line)
    {
        if (file_exists($fileName)) {
            $fp = fopen($fileName, 'a');
        } else {
            $fp = fopen($fileName, 'w');
        }
        foreach ($line as $item) {
            $l = implode(chr(9),$item).chr(13).chr(10);
            fwrite($fp,$l);
        }
        fclose($fp);
    }

    /**
     * logs
     *
     * @param string
     */

    public function log($mess)
    {
        $vendorId = $this->getVendor()->getId();
        $this->getHelper()->log($vendorId, $mess);
    }

}
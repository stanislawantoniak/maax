<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Http extends Mage_Core_Helper_Http
{
    protected $_httpXForwardedAddr;

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Retrieve Client X HTTP Forwarded Address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getXForwardedAddr($ipToLong = false)
    {
        if (is_null($this->_httpXForwardedAddr)) {
            if (!$this->_httpXForwardedAddr) {
                $header = $this->_getRequest()->getServer('HTTP_X_FORWARDED_FOR');

                if ($header){
                    $set = array();
                    $ips = explode(",", $header);
                    foreach ($ips as $ip){
                        $this->_httpXForwardedAddr = trim($this->_commonHelper()->escapeHtml($ip));
                        break;
                    }
                }
            }
        }

        if (!$this->_httpXForwardedAddr) {
            return false;
        }

        return $ipToLong ? ip2long($this->_httpXForwardedAddr) : $this->_httpXForwardedAddr;
    }

}
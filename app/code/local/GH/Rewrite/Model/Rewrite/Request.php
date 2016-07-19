<?php
/**
 * overwrite Mage_Core_Model_Url_Rewrite_Request
 */
class GH_Rewrite_Model_Rewrite_Request extends Mage_Core_Model_Url_Rewrite_Request {

    
    /**
     * repair queryString
     */

    protected function _processRedirectOptions()
    {
        $isPermanentRedirectOption = $this->_rewrite->hasOption('RP');

        $external = substr($this->_rewrite->getTargetPath(), 0, 6);
        if ($external === 'http:/' || $external === 'https:') {
            $destinationStoreCode = $this->_app->getStore($this->_rewrite->getStoreId())->getCode();
            $this->_setStoreCodeCookie($destinationStoreCode);
            $this->_sendRedirectHeaders($this->_rewrite->getTargetPath(), $isPermanentRedirectOption);
        }

        $targetUrl = $this->_request->getBaseUrl() . '/' . $this->_rewrite->getTargetPath();
        $storeCode = $this->_app->getStore()->getCode();
        if (Mage::getStoreConfig('web/url/use_store') && !empty($storeCode)) {
            $targetUrl = $this->_request->getBaseUrl() . '/' . $storeCode . '/' . $this->_rewrite->getTargetPath();
        }
        if ($this->_rewrite->hasOption('R') || $isPermanentRedirectOption) {
            if (Mage::helper('umicrosite')->getCurrentVendor() && Mage::getStoreConfig('zolagoos/microsite/subdomain_level') == 1) {
                $this->_sendRedirectHeaders("/" . Mage::helper('umicrosite')->getCurrentVendor()->getUrlKey() . $targetUrl, $isPermanentRedirectOption);
            } else {
                $this->_sendRedirectHeaders($targetUrl, $isPermanentRedirectOption);
            }
        }

        $queryString = $this->_getQueryString();
        if ($queryString) {
            $parsed = parse_url($targetUrl);
            if (!empty($parsed['query'])) {            
                $targetUrl .= '&' . $queryString;
            } else {
                $targetUrl .= '?' . $queryString;
            }
        }

        $this->_request->setRequestUri($targetUrl);
        $this->_request->setPathInfo($this->_rewrite->getTargetPath());
        return $this;
    }

}
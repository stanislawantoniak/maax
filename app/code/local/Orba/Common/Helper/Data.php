<?php

class Orba_Common_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const LOG_SCOPE_AJAX = 'ajax';
    
    /**
     * Logs a message to /var/log/orbacommon_{$scope}.log
     * 
     * @param string $message
     * @param string $scope
     */
    public function log($message = '', $scope = '') {
        Mage::log($message, null, 'orbacommon' . ($scope ? '_' . $scope : '') . '.log');
    }
    
    /**
     * Converts timestamp to GMT date
     * 
     * @param int $time
     * @return string
     */
    public function timestampToGmtDate($time) {
        return gmdate('D, d M Y H:i:s', $time) . ' GMT';
    }
    
    /**
     * Gets OrbaLib URL
     * 
     * @return string
     */
    public function getJsLibUrl() {
        $base = Mage::getUrl('orbacommon/js/lib', array(
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
        ));
        $hash = md5(Mage::app()->getLayout()->createBlock('core/template')->setTemplate('orbacommon/js/lib.phtml')->toHtml());
        return $base . '?' . $hash;
    }

    /**
     * formatting text (polska odmiana)
     * 
     * @param $counter
     *
     * @return string
     */
    public function formatOrdersText($counter) {
        $suffix = $this->__('zamówień');
        if ($counter == 1) {
            $suffix = $this->__('zamówienie');
        } else if (!in_array($counter,array(11,12,13))) {
            if (in_array($counter % 10,array(2,3,4))) {
                $suffix = $this->__('zamówienia');
            }
        }
        return $counter.' '.$suffix;
    }
    

    /**
     * @param     $val
     * @param int $decimals
     *
     * @return float
     */
    function ceildec($val, $decimals = 2)
    {
        return ceil($val * pow(10, $decimals)) / pow(10, $decimals);
    }
}
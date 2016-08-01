<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Mobile Helper
 */
class Magpleasure_Common_Helper_Mobile extends Mage_Core_Helper_Abstract
{
    /**
     * iPhone Client Response
     */
    const IPHONE_CLIENT = 'iPhone';

    /**
     * Android Client Response
     */
    const ANDROID_CLIENT = 'Android';

    /**
     * Blackberry Client Response
     */
    const BLACKBERRY_CLIENT = 'BlackBerry';

    protected function _checkUserAgent($targetPlatform)
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])){
            return (strpos($_SERVER['HTTP_USER_AGENT'], $targetPlatform) !== false);
        }
        return false;
    }

    public function isAndroid()
    {
        return $this->_checkUserAgent(self::ANDROID_CLIENT);
    }

    public function isiPhone()
    {
        return $this->_checkUserAgent(self::IPHONE_CLIENT);
    }

    public function isBlackBerry()
    {
        return $this->_checkUserAgent(self::BLACKBERRY_CLIENT);
    }

    public function isMobile()
    {
        return $this->isAndroid() || $this->isiPhone() || $this->isBlackBerry();
    }
}
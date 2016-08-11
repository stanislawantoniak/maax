<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Shipping extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udropship_shipping';
    protected $_eventObject = 'shipping';

    protected function _construct()
    {
        $this->_init('udropship/shipping');
    }

    public function getStoreTitle($store = null)
    {
        $storeId = Mage::app()->getStore($store)->getId();
        $titles = (array)$this->getStoreTitles();

        if (isset($titles[$storeId])) {
            return $titles[$storeId];
        } elseif (isset($titles[0]) && $titles[0]) {
            return $titles[0];
        }

        return $this->getShippingTitle();
    }

    public function getStoreTitles()
    {
        if (!$this->hasStoreTitles()) {
            $titles = $this->_getResource()->getStoreTitles($this->getId());
            $this->setStoreTitles($titles);
        }

        return $this->_getData('store_titles');
    }

    protected $_profile='default';
    public function useProfile($profile=null)
    {
        if ($profile instanceof ZolagoOs_OmniChannel_Model_Vendor
            && Mage::helper('udropship')->isUdsprofileActive()
        ) {
            if ($profile->getShippingProfileUseCustom()) {
                $vMethods = $profile->getVendorShippingMethods(true);
                $vMethods = !empty($vMethods[$this->getId()]) ? $vMethods[$this->getId()] : array();
                $this->_profile = $vMethods;
                return $this;
            } else {
                $profile = $profile->getShippingProfile();
            }
        } elseif ($profile instanceof Varien_Object) {
            $profile = $profile->getShippingProfile();
        }
        if (null===$profile
            || !Mage::helper('udropship')->isUdsprofileActive()
            || !Mage::helper('udsprofile')->hasProfile($profile)
        ) {
            $this->_profile='default';
        } else {
            $this->_profile=$profile;
        }
        return $this;
    }
    public function resetProfile()
    {
        return $this->useProfile();
    }
    public function getAllSystemMethods()
    {
        return $this->getSystemMethods(null, true);
    }
    public function getSystemMethods($carrier=null, $all=false)
    {
        if (is_array($this->_profile)) {
            $methods = $this->_profile;
        } else {
            $methods = $this->getSystemMethodsByProfile($this->_profile);
        }
        if (!is_array($methods)) return null;
        if (null===$carrier) return $methods;
        if (!is_array(@$methods[$carrier]) || empty($methods[$carrier])) return @$methods[$carrier];
        reset($methods[$carrier]);
        return !$all ? current($methods[$carrier]) : $methods[$carrier];
    }
}
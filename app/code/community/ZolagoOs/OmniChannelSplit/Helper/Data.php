<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isActive($store=null)
    {
        return Mage::getStoreConfig('carriers/udsplit/active', $store);
    }
}
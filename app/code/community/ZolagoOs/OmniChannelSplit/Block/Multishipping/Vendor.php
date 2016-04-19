<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Block_Multishipping_Vendor extends ZolagoOs_OmniChannelSplit_Block_Cart_Vendor
{
    protected function _construct()
    {
        $this->setTemplate('unirgy/dsplit/multishipping/vendor.phtml');
    }

}
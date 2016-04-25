<?php

class Zolago_DropshipVendorProduct_Model_Observer 
	extends ZolagoOs_OmniChannelVendorProduct_Model_Observer
{
    /**
	 * Clear afetr laod quote better performance
	 * @todo possible bugs not compatibile with unirgy?
	 * @param type $observer
	 * @return type
	 */
    public function sales_quote_load_after($observer)
    {
        if(Mage::helper("zolagocommon")->isUserDataRequest()){
			return;
		}
		parent::sales_quote_load_after($observer);
    }

}
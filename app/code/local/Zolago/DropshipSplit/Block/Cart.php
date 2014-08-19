<?php

class Zolago_DropshipSplit_Block_Cart extends Unirgy_DropshipSplit_Block_Cart
{
    public function getItems()
    {
		$a = $this->getQuote()->getShippingAddress();
        $aRates = $a->getGroupedAllShippingRates();
		
				
		/**
		 * Fix rate quto query
		 */
		if(!$aRates){
			$a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
			$a->setCollectShippingRates(true);
			$a->collectShippingRates();
			$aRates = $a->getGroupedAllShippingRates();
		}

        return parent::getItems();
    }

}
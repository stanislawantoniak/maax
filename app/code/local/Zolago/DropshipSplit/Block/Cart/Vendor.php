<?php

class Zolago_DropshipSplit_Block_Cart_Vendor extends Unirgy_DropshipSplit_Block_Cart_Vendor
{
	/**
	 * @return null | Mage_Sales_Model_Quote_Address_Rate
	 */
	public function getMinimalShippingRate() {
		if($groups=$this->getEstimateRates()){
		
			foreach($groups as $code=>$rates){
				list($minimalKey,$minialRate) = each($rates);


				$mininal = $minialRate->getPrice();

				foreach($rates as $key=>$rate){
					/* @var $rate Mage_Sales_Model_Quote_Address_Rate */
					if($rate->getPrice()<$minialRate->getPrice()){
						$minialRate = $rate;
					}

				}
			}
			
			return $rate;
		}
		return null;
	}

}
<?php

class Zolago_DropshipSplit_Block_Cart_Vendor extends Unirgy_DropshipSplit_Block_Cart_Vendor
{
	const NOMINAL_PRICE = 500;
	
	/**
	 * @return boolean
	 */
	public function isHintAvailable() {
		return $this->getSubtotal()<$this->getNominalPrice() && !$this->isFreeShipping(); 
	}
	
	/**
	 * @return boolean
	 */
	public function isFreeShipping() {
		return ($this->getMinimalShippingRate() && $this->getMinimalShippingRate()->getPrice()==0);
	}
	
	
	/**
	 * @todo implement
	 * @return decimal
	 */
	public function getNominalPrice() {
		return self::NOMINAL_PRICE;
	}
	
	/**
	 * @return null | Mage_Sales_Model_Quote_Address_Rate
	 */
	public function getMinimalShippingRate() {
		if(!$this->hasData("minimal_shipping_rate")){
			$this->setData("minimal_shipping_rate", null);
			if($groups=$this->getEstimateRates()){
				$minialRate = null;
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
				$this->setData("minimal_shipping_rate", $minialRate);
			}
		}
		return $this->getData("minimal_shipping_rate");
	}

}
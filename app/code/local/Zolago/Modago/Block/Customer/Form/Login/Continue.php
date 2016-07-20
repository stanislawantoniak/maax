<?php
class Zolago_Modago_Block_Customer_Form_Login_Continue extends Mage_Cms_Block_Block{
	
	const BLOCK_ID_NORMAL = "login-continue-normal";
	const BLOCK_ID_CHECKOUT = "login-continue-checkout";
	
	/**
	 * @return string
	 */
	public function getBlockId() {
		if($this->getIsCheckout()){
			return self::BLOCK_ID_CHECKOUT;
		}
		return self::BLOCK_ID_NORMAL;
	}
	
	/**
	 * Is checkout mode
	 * @return bool
	 */
	public function getIsCheckout() {
		return $this->getRequest()->getParam("is_checkout");
	}
}
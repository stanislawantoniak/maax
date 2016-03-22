<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 13.08.2014
 * 
 * @method Zolago_DropshipSplit_Block_Cart getParentBlock()
 */

class Zolago_Modago_Block_Checkout_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    /**
     * Return total shipping cost
     *
     * @return float | null
     */
    public function getShippingTotal()
    {

        return $this->getParentBlock()->getShippingTotal();
    }
	
    /**
     * Ignore total shippign (not avaialble)
     * @return bool
     */
    public function getIgnoreTotal()
    {
        return $this->getParentBlock()->getIgnoreTotal();
    }
	
	/**
	 * @return string
	 */
	public function getCheckoutUrl() {
		return Mage::getUrl("checkout/singlepage");
	}
} 
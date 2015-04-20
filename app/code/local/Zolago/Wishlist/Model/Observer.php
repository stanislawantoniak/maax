<?php
/**
 * zolago wishlist observer
 */

class Zolago_Wishlist_Model_Observer {
	
	/* @var $_cookie Mage_Core_Model_Cookie */
	protected $_cookie;


	public function setCookieModel($cookie) {
		$this->_cookie = $cookie;
		return $this;
	}
	
	public function getCookieModel() {
		if(!$this->_cookie){
			$this->_cookie = Mage::getModel('core/cookie');
		}
		return $this->_cookie;
	}

    /**
     * increase favourite flag
     */
    public function wishlistAddAfter($obj) {
        $items = $obj->getItems();

        if (empty($items)) return;

        foreach ($items as $item) {
            $productId = $item->getProductId();
//               $attribute = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY,'wishlist_count');
            $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, 'wishlist_count', 0)+1;
            Mage::getSingleton('catalog/product_action')->updateAttributes(array($productId),array ('wishlist_count'=>$value),0);
        }
        return $this;
    }
    /**
     * decrease favourite flag
     */
    public function wishlistDelAfter($obj) {
        $item = $obj->getItem();

        if (empty($item)) return;

        $productId = $item->getProductId();
        $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, 'wishlist_count', 0)-1;
        Mage::getSingleton('catalog/product_action')->updateAttributes(array($productId),array ('wishlist_count'=>(($value>0)? $value:0)),0);
        return $this;
    }
	
	/**
	 * Handle after login wishlist merge
	 */
	public function handleCustomerLogin($observer) {
		$customer = $observer->getEvent()->getData("customer");
		/* @var $customer Mage_Customer_Model_Customer */
		$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer);
		/* @var $wishlist Mage_Wishlist_Model_Wishlist */
		$cookieWishlist = Mage::helper("wishlist")->getCookieWishlist();
		/* @var $cookieWishlist Mage_Wishlist_Model_Wishlist */

        $sharingCode = Mage::helper("wishlist")->getRawSharingCode();
        Mage::unregister('sharing_code');
        Mage::register('sharing_code', $sharingCode, true);

		if($cookieWishlist->getId()){
			// Move all items from cookie to custoemr wishlist
			$cookieItems = $cookieWishlist->getItemCollection();
			if($cookieItems->count() && !$wishlist->getId()){
				$wishlist->setCustomerId($customer->getId());
				$wishlist->save();
			}
			
			$items = $wishlist->getItemCollection();
			$hasItems = $items->count();
			
			foreach ($cookieItems as $cookieItem){
				/* @var $cookieItem Mage_Wishlist_Model_Item */
				if($hasItems){
					// Add existing cookie items to wishlit items
					foreach($items as $item){
						/* @var $items Mage_Wishlist_Model_Item */
						if($item->getProductId()==$cookieItem->getProductId()){
							$item->setQty($item->getQty()+$cookieItem->getQty());
							$item->save();
							continue 2;
						}
					}
				}
				// If not exits move to customer wishlist
				$cookieItem->setWishlistId($wishlist->getId());
				$cookieItem->save();
			}
			
			// Remove anonyous wishlist
			$cookieWishlist->delete();
			
			// Recalculate 
			Mage::helper("wishlist")->calculate();
		}
		
		$this->getCookieModel()->delete(
				Zolago_Wishlist_Helper_Data::COOKIE_NAME
		);
	}
}
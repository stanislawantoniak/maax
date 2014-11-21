<?php
class Zolago_Wishlist_Helper_Data extends Mage_Wishlist_Helper_Data{
    
	const COOKIE_NAME = "wishlist_code";
	
    protected $_wishlist;
	
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
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer() {
		$parent = parent::getCustomer() ;
		if($parent){
			return $parent;
		}
		return Mage::helper('zolagocustomer/ghost')->getCustomer();
	}
	
	/**
	 * Get anonymous wishlist
	 * @return Mage_Wishlist_Model_Wishlist
	 */
	public function getCookieWishlist() {
			$wishlist = Mage::getModel("wishlist/wishlist");
			/* @var $wishlist Mage_Wishlist_Model_Wishlist */
			
			$cookie = $this->getCookieModel()->get(self::COOKIE_NAME);
			
			if($cookie){
				$wishlist->load($cookie, "sharing_code");
			}
			return $wishlist;
	}
	
	
	/**
	 * Get avaiable wishlist
	 * @return Mage_Wishlist_Model_Wishlist
	 */
	public function getWishlist() {
		$session = Mage::getSingleton("customer/session");
		/* @var $session Mage_Customer_Model_Session */
		if(!$session->isLoggedIn()){
			$wishlist = Mage::getModel("wishlist/wishlist");
			/* @var $wishlist Mage_Wishlist_Model_Wishlist */
			
			$cookie = $this->getCookieModel()->get(self::COOKIE_NAME);
			
			if($cookie){
				$wishlist->load($cookie, "sharing_code");
			}
			
			if(!$wishlist->getId()){
				$ghost = Mage::helper('zolagocustomer/ghost')->getCustomer();
				$wishlist->setCustomerId($ghost->getId());
				if($cookie){
					$wishlist->setSharingCode($cookie);
				}else{
					$wishlist->generateSharingCode();
					$this->getCookieModel()->set(self::COOKIE_NAME, $wishlist->getSharingCode());
				}
			}
			
			
			return $wishlist;
		}
		return parent::getWishlist();
	}
	
	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return boolean
	 */
    public function productBelongsToMyWishlist(Mage_Catalog_Model_Product $product) {
		
        $coll = Mage::getResourceModel("wishlist/item_collection");
        /* @var $coll Mage_Wishlist_Model_Resource_Item_Collection */
        
        $coll->addWishlistFilter($this->getWishlist());
        $coll->addFieldToFilter("product_id", $product->getId());
        
        return (bool)$coll->count();
    }
    
    /**
     * @param int $customerId
     * @return Mage_Wishlist_Model_Wishlist
     */
    protected function _getWishlist($customerId){
        if(!$this->_wishlist){
            $wishlist = Mage::getModel('wishlist/wishlist');
            if($customerId){
                $wishlist->loadByCustomer($customerId, true);
            }
            $this->_wishlist = $wishlist;
        }
        return $this->_wishlist;
    }


}
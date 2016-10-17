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
	
	/**
	 * 
	 * @return Mage_Core_Model_Cookie
	 */
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
	
	public function getRawSharingCode() {
        return $this->getCookieModel()->get(self::COOKIE_NAME);
    }

	/**
	 * Get avaiable wishlist
	 * @return Mage_Wishlist_Model_Wishlist
	 */
	public function getWishlist() {
		$session = Mage::getSingleton("customer/session");
		/* @var $session Mage_Customer_Model_Session */
		
		// If wishlis registered by action controller just return it
		if(Mage::registry('wishlist')){
			return Mage::registry('wishlist');
		}
		
		if(is_null($this->_wishlist) && !$session->isLoggedIn()){
			$wishlist = Mage::getModel("wishlist/wishlist");
			/* @var $wishlist Mage_Wishlist_Model_Wishlist */
			
			// First try to load by persistent
			$persistentHelper = Mage::helper('persistent/session');
			/* @var $persistentHelper Mage_Persistent_Helper_Session */
			
			if($persistentHelper->isPersistent() && $persistentHelper->getSession()->getCustomerId()){
				$customerId = $persistentHelper->getSession()->getCustomerId();
				$wishlist->loadByCustomer($customerId);
				// No wishlist existing - set customer id to new wishlist
				if(!$wishlist->getId()){
					$wishlist->setCustomerId($customerId);
				}
				return $wishlist;
			}
			
			// Second make cookie for ghost customer to emulate
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
					$this->getCookieModel()->set(self::COOKIE_NAME, $wishlist->getSharingCode(), true);
				}
			}
			
			// Parent function should recoginize as not null and return it 
			$this->_wishlist = $wishlist;
		}
		return parent::getWishlist();
	}
    
    /**
     * 
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
	public function getWishlistItems() {
	    $wishlist = $this->getWishlist();
	    $collection = $wishlist->getItemCollection();
	    $brandId = Mage::helper('zolagocatalog')->getBrandId();
	    $collection->getSelect()->distinct()
	        ->joinLeft('catalog_product_entity_int as b', 'b.entity_id=product_id and b.store_id = 0 and b.attribute_id = '.$brandId, 'b.value as brand_id_default')
	        ->joinLeft('catalog_product_entity_int as c', 'c.entity_id=product_id and c.store_id = main_table.store_id and c.attribute_id = '.$brandId, 'c.value as brand_id');
             return $collection;
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
     * Gets only collection of products that is in wishlist for selected products
     *
     * @param $productsIds array
     * @return Mage_Wishlist_Model_Resource_Item_Collection|Object
     */
    public function checkProductsBelongsToMyWishlist($productsIds) {
        $coll = Mage::getResourceModel("wishlist/item_collection");
        /* @var $coll Mage_Wishlist_Model_Resource_Item_Collection */
        if (!empty($productsIds)) {
            $coll->addWishlistFilter($this->getWishlist());
            $coll->addFieldToFilter("product_id", array('in' => $productsIds));
        }
        return $coll;
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
<?php
class Zolago_Wishlist_Helper_Data extends Mage_Core_Helper_Abstract{
    
    protected $_wishlist;
    
    public function productBelongsToMyWishlist(Mage_Catalog_Model_Product $product) {
        $session = Mage::getSingleton("customer/session");
        $customerId = $session->getCustomerId();
        
        if(!$customerId){
            return false;
        }
        
        $coll = Mage::getResourceModel("wishlist/item_collection");
        /* @var $coll Mage_Wishlist_Model_Resource_Item_Collection */
        
        $coll->addCustomerIdFilter($customerId);
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
<?php
class Zolago_Wishlist_MergeTest extends ZolagoDb_TestCase{
	
	const CUSTOMER_EMAIL = "test@zolago.pl";
	
	public function testScenario() {
		if (!no_coverage()) {
			$this->markTestSkipped('coverage');
			return;
		}
		$helper = Mage::helper("wishlist");
		/* @var $helper Zolago_Wishlist_Helper_Data */
		$helper->setCookieModel($this->_getCookieModel());
		// Make anonymous wishlist
		$cookieWhislist = $helper->getWishlist();
		/* @var $cookieWhislist Mage_Wishlist_Model_Wishlist */
		
		$wishlist = Mage::getModel("wishlist/wishlist");
		/* @var $wishlist Mage_Wishlist_Model_Wishlist */
		
		$product = $this->_getProduct();
		$customer = $this->_getCustomer();
		$session = Mage::getSingleton('customer/session');
		/* @var $session Mage_Customer_Model_Session */
		
		// Make sure cookie wishlist exits
		if(!$cookieWhislist->getId()){
			$cookieWhislist->save();
		}
		
		// Add product to anounymous wishlist (one item)
		$cookieWhislist->addNewItem($product);
		
		// Load wishlist by customer
		$wishlist->loadByCustomer($customer);
		
		$beforeMergeQty = 0;
		
		// Before qty
		if($wishlist->getId()){
			foreach ($wishlist->getItemCollection() as $item){
				/* @var $item Mage_Wishlist_Model_Item */
				if($item->getProductId()==$product->getId()){
					$beforeMergeQty = $item->getQty();
					break;
				}
			}
		}
		
		// Set observer model
		Mage::getSingleton('zolagowishlist/observer')->setCookieModel(
			$this->_getCookieModel()
		);
		
		// Log in customer
		$session->setCustomerAsLoggedIn($customer);
		
		// Wishlist should exists after merge via event
		$wishlistCompare = $helper->getWishlist();
		/* @var $wishlistCompare Mage_Wishlist_Model_Wishlist */
		if(!$wishlistCompare->getId() || $wishlist->getId()==$cookieWhislist->getId()){
			$this->fail("Wishlist dosnt exists");
		}
		
		$afterMergeQty = -1;

		foreach($wishlistCompare->getItemCollection() as $item){
			/* @var $item Mage_Wishlist_Model_Item */
			if($item->getProductId()==$product->getId()){
				$afterMergeQty = (int)$item->getQty();
				break;
			}
		}
		
		// Was element added
		$this->assertEquals($beforeMergeQty+1, $afterMergeQty);
		
		// Cookie wishlist sould by removed
		$cookieWishslitCompare = Mage::getModel("wishlist/wishlist")->
				load($cookieWhislist->getId());
		/* @var $wishlistCompare Mage_Wishlist_Model_Wishlist */
		$this->assertNull($cookieWishslitCompare->getId());
		
	}
	
	/**
	 * @return Mage_Catalog_Model_Product
	 * @throws Exception
	 */
	protected function _getProduct() {
		$collection = Mage::getResourceModel("catalog/product_collection");
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->setPageSize(1);
		$collection->setOrder("entity_id", "desc");
		
		if(!$collection->count()){
			throw new Exception("Empty colleciton");
		}
		foreach($collection as $product){
			/* @var $product Mage_Catalog_Model_Product */
			return $product->load($product->getId());
		}
	}
	
	/**
	 * @return Mage_Customer_Model_Customer
	 */
	protected function _getCustomer() {
		$customer = Mage::getModel("customer/customer");
		/* @var $customer Mage_Customer_Model_Customer */
		$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
		
		$customer->loadByEmail(self::CUSTOMER_EMAIL);
		
		if(!$customer->getId()){
			$customer->setFirstname("Zolago");
			$customer->setLastname("Test");
			$customer->setEmail(self::CUSTOMER_EMAIL);
			$customer->save();
		}
		
		return $customer;
	}
	
	protected function _getCookieModel() {
		if(!$this->_cookieModel){
			$this->_cookieModel = new CookieMockup();
		}
		return $this->_cookieModel;
	}
	
}


class CookieMockup {
		
	protected $_cookieData = array();

	public function get($cookieName) {
		return $this->_cookieData[$cookieName];
	}
	
	public function set($cookieName, $value) {
		$this->_cookieData[$cookieName] = $value;
	}
	
	public function delete($cookieName) {
		unset($this->_cookieData[$cookieName]);
	}
}
?>

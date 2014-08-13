<?php

require_once Mage::getModuleDir('controllers', 'Zolago_Wishlist') . DS . "IndexController.php";


class Orba_Common_Ajax_WishlistController extends Zolago_Wishlist_IndexController {
	
	/**
	 * Add item to favorites
	 * Request params:
	 *		product:			<int> | reqired
	 *		related_product:	<int> | optional	
	 *		super_attribute:	<array> attributeId=>optionId pairs | optional
	 *		qty:				<int> | optional default 1
	 * Response:
	 *		@see Orba_Common_Ajax_CustomerController::get_account_informationAction();
	 */
	public function addAction() {
		
		// Make regular request
		$this->_addItemToWishList();
		$this->_clearRedirect();
		
		if(($error=$this->_getFirstSessionError())!==null){
			$this->_sendJson(0, $error);
			return;
		}
		
		// Send correct data
		$this->_forward("get_account_information", "ajax_customer", "orbacommon");
	}
	
	/**
	 * Remove item from favourites
	 * Request params (one of them needed):
	 *		item:				<int> | optional - wishlist item id
	 *		product:			<int> | optional - product of wishlist item id
	 * Response:
	 *		@see Orba_Common_Ajax_CustomerController::get_account_informationAction();
	 */
	public function removeAction() {
		
		$wishlist  = $this->_getWishlist();

		// Find item id if product id setted;
		if(!$this->getRequest()->getParam("item")){
			$productId = $this->getRequest()->getParam("product");
			foreach($wishlist->getItemCollection() as $item){
				if($item->getProductId()==$productId){
					$this->getRequest()->setParam("item", $item->getId());
					break;
				}
			}
		}
		
		$error = null;
		$listItem = $wishlist->getItem($this->getRequest()->getParam("item"));
		if(!$listItem || !$listItem->getId()){
			$error = $this->__("Wrong item/product specified.");
		}
		
		// Remove by paren action
		parent::removeAction();
		$this->_clearRedirect();
		
		if($error || ($error=$this->_getFirstSessionError())!==null){
			$this->_sendJson(0, $error);
			return;
		}
		
		// Send correct data
		$this->_forward("get_account_information", "ajax_customer", "orbacommon");
		
	}
	
	/**
	 * @param type $coreRoute 
	 * no action
	 */
    public function norouteAction($coreRoute = null){
		return;
	}
	
	/**
	 * @param boolean $status
	 * @param mixed $content
	 */
	protected function _sendJson($status, $content) {
		$this->getResponse()->
			setHeader("Content-type", "application/json")->
			setBody(Mage::helper('core')->jsonEncode(array(
				'status'	=> (bool)$status,
				'content'	=> $content
			))
		);
	}
	
	/**
	 * Clear before redirect
	 */
	protected function _clearRedirect() {
		$this->norouteAction();
		$this->getResponse()->clearHeader("Location");
		$this->getResponse()->setHttpResponseCode(200);
	}
	
	/**
	 * @return string | null
	 */
	protected function _getFirstSessionError() {
		// Get and clear Messages and process errors
		$messages = $this->_getSession()->getMessages(true);
		if($messages instanceof Mage_Core_Model_Message_Collection){
			foreach($messages->getErrors() as $error){
				return $error->getCode();
			}
		}
	}
	
	/**
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getSession() {
		return Mage::getSingleton('customer/session');
	}
	
}
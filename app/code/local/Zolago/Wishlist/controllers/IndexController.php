<?php

require_once Mage::getModuleDir('controllers', 'Mage_Wishlist') . DS . "IndexController.php";

class Zolago_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
	public function preDispatch() {
		$this->skipAuthentication();
		return parent::preDispatch();
	}
	
    /**
     * Retrieve wishlist object
     * @param int $wishlistId
     * @return Mage_Wishlist_Model_Wishlist|bool
     */
    protected function _getWishlist($wishlistId = null)
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            if (!$wishlistId) {
                $wishlistId = $this->getRequest()->getParam('wishlist_id');
            }
            $session = Mage::getSingleton('customer/session');
            /* @var Mage_Wishlist_Model_Wishlist $wishlist */
				$wishlist = Mage::getModel('wishlist/wishlist');
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
				$wishlist = Mage::helper('wishlist')->getWishlist();
			}
			
			// Loged in session - Load by customer
			if($session->isLoggedIn()){
				$wishlist->loadByCustomer($session->getCustomer());
				// Not exist? Save.
				if(!$wishlist->getId()){
					$wishlist->setCustomerId($session->getCustomerId());
					$wishlist->save();
				}
			}
			
            if ($session->isLoggedIn() && $session->getCustomerId()!=$wishlist->getCustomerId()) {
                Mage::throwException(
                    Mage::helper('wishlist')->__("Requested wishlist doesn't exist")
                );
            }
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
            return false;
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Wishlist could not be created.')
            );
            return false;
        }

        return $wishlist;
    }
	
	/**
	 * force save wishlist before add item
	 * @return void
	 */
	protected function _addItemToWishList() {
		$wishlist = $this->_getWishlist();
		if(!$wishlist->getId()){
			$wishlist->save();
		}
		parent::_addItemToWishList();
	}
	
	/**
	 * force save wishlist before add item
	 * @return type
	 */
	public function fromcartAction() {
		$wishlist = $this->_getWishlist();
		if(!$wishlist->getId()){
			$wishlist->save();
		}
		return parent::fromcartAction();
	}
	
}

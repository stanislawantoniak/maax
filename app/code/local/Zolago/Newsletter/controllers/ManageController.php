<?php
require_once Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php';
class Zolago_Newsletter_ManageController extends Mage_Newsletter_ManageController {
	public function saveAction() {
		if (!$this->_validateFormKey()) {
			return $this->_redirect('customer/account');
		}
		try {
			$isSubscribed = (boolean)$this->getRequest()->getParam('is_subscribed', false) ? 1 : 0;
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$customer
				->setStoreId(Mage::app()->getStore()->getId())
				->setIsSubscribed($isSubscribed)
				->save();

			if($isSubscribed && $customer->getConfirmMsg()) {
				Mage::getSingleton('customer/session')->addSuccess($this->__("Your subscribtion has been saved.<br />To start receiving our newsletter you have to confirm your e-mail by clicking confirmation link in e-mail that we have just sent to you.<br />Newsletter setting in your account will be changed after e-mail confirmation."));
			} elseif($isSubscribed) {
				Mage::getSingleton('customer/session')->addSuccess($this->__("The subscription has been saved."));
			} elseif (!$isSubscribed) {
				Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
			Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
		}
		$this->_redirectReferer();
	}


	public function ajaxSaveAction() {
		if (!$this->_validateFormKey()) {
			return $this->_redirect('customer/account');
		}
		try {
			$isSubscribed = (boolean)$this->getRequest()->getParam('is_subscribed', false) ? 1 : 0;
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$customer
				->setStoreId(Mage::app()->getStore()->getId())
				->setIsSubscribed($isSubscribed)
				->save();

			if($isSubscribed && $customer->getConfirmMsg()) {
				$result = array('popup' => 1, 'refresh' => 0); //show popup
			} else {
				$result = array('popup' => 0, 'refresh' => 1); //refresh page;
			}

			$this->_setSuccessResponse($result);

		} catch (Exception $e) {
			Mage::logException($e);
			Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
		}
	}

	/**
	 * Prepares JSON response with results
	 *
	 * @param array $result
	 * @param int $expires In seconds
	 * @param int $lastModified Timestamp
	 */
	protected function _setSuccessResponse($result, $expires = null, $lastModified = null) {
		$_helper = Mage::helper('orbacommon');
		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-type', 'application/json', true);
		//->setHeader('Set-Cookie', '', true);
		if ($expires) {
			$this->getResponse()
				->setHeader('Cache-Control', 'public, cache, must-revalidate, post-check='.$expires.', pre-check='.$expires.', max-age='.$expires, true)
				->setHeader('Pragma', 'cache', true)
				->setHeader('Expires', $_helper->timestampToGmtDate(time() + $expires), true);
		} else {
			$this->getResponse()
				->setHeader('Pragma', 'no-cache', true)
				->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
				->setHeader('Expires', $_helper->timestampToGmtDate(0), true);
		}
		if ($lastModified) {
			$this->getResponse()->setHeader('Last-Modified', $_helper->timestampToGmtDate($lastModified), true);
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}
<?php

/**
 * Class ZolagoOs_LoyaltyCard_CardController
 */
class ZolagoOs_LoyaltyCard_CardController extends Zolago_Dropship_Controller_Vendor_Abstract {
	
	public function indexAction() {
		Mage::register('as_frontend', true);
		$this->_renderPage(null, 'zos-loyalty-card');
	}

	public function editAction() {
		$helper = Mage::helper('zosloyaltycard');
		Mage::register('as_frontend', true);
		$id = $this->getRequest()->getParam('id');
		$card = $this->_initModel($id);
		$vendor = $this->_getSession()->getVendor();

		// Existing
		if ($card->getId()) {
			if ($card->getVendorId() != $vendor->getId()) {
				$this->_getSession()->addError($helper->__("Card does not exists"));
				return $this->_redirect("*/*");
			}
		} elseif ($this->getRequest()->getParam('id', null) !== null) {
			$this->_getSession()->addError($helper->__("Card does not exists"));
			return $this->_redirect("*/*");
		}

		// Process request & session data
		$sessionData = $this->_getSession()->getFormData();
		if (!empty($sessionData)) {
			$card->addData($sessionData);
			$this->_getSession()->setFormData(null);
		}
		$this->_renderPage(null, 'zos-loyalty-card');
	}

	public function newAction() {
		$this->_forward('edit');
	}

	/**
	 * Save new/existing card
	 */
	public function saveAction()
	{
		/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
		$helper = Mage::helper("zosloyaltycard");
		if (!$this->getRequest()->isPost()) {
			return $this->_redirectReferer();
		}
		// Form key valid?
		$formKey = Mage::getSingleton('core/session')->getFormKey();
		$formKeyPost = $this->getRequest()->getParam('form_key');
		if ($formKey != $formKeyPost) {
			return $this->_redirectReferer();
		}
		$cardId = $this->getRequest()->getPost('id');
		$card = $this->_initModel($cardId);
		$vendor = $this->_getSession()->getVendor();

		// Try save
		$data = $this->getRequest()->getParams();
		unset($data['id']);
		unset($data['form_key']);
		// read only on edit page
		unset($data['created_at']);
		unset($data['updated_at']);

		$this->_getSession()->setFormData(null);

		try {
			// Remove 'empty' kids
			$_kids = array();
			foreach ($data['kids'] as $key => $kid) {
				if (!empty($kid['first_name']) && !empty($kid['birthdate'])) {
					$_kids[] = $kid;
				}
			}
			$data['kids'] = $_kids;

			$card->addData($data);
			
			// Fix empty value
			if ($card->getId() == "") {
				$card->setId(null);
			}
			if ($card->isObjectNew()) {
				// Set default store
				/** @var Mage_Core_Model_Website $website */
				$website = Mage::app()->getWebsite('base'); // ...
				$card->setStoreId($website->getDefaultStore()->getId());
				// Set Vendor Owner
				$card->setVendorId($vendor->getId());
				// Set operator
				$operatorId = null;
				if($this->_getSession()->isOperatorMode()) {
					$operatorId = $this->_getSession()->getOperator()->getId();
				}
				$card->setOperatorId($operatorId);
			}

			/**
			 * @event loyalty_card_save_before
			 * @event loyalty_card_save_after
			 */
			$card->save();
			
			$this->_getSession()->addSuccess($helper->__("Card '%s' saved", $card->getCardNumber()));
			
			return $this->_redirect("*/*");
			
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_getSession()->setFormData($data);
			return $this->_redirectReferer();
		} catch (Exception $e) {
			$this->_getSession()->addError($helper->__("Some error occurred"));
			$this->_getSession()->setFormData($data);
			Mage::logException($e);
			return $this->_redirectReferer();
		}
	}
	
	public function deleteAction() {
		/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
		$helper = Mage::helper("zosloyaltycard");
		if (!$this->getRequest()->isPost()) {
			return $this->_redirectReferer();
		}
		// Form key valid?
		$formKey = Mage::getSingleton('core/session')->getFormKey();
		$formKeyPost = $this->getRequest()->getParam('form_key');
		if ($formKey != $formKeyPost) {
			return $this->_redirectReferer();
		}
		$cardId = $this->getRequest()->getPost('id');
		$deleteType = $this->getRequest()->getPost('delete_type');
		$card = $this->_initModel($cardId);
		$vendor = $this->_getSession()->getVendor();

		try {
			// Existing
			if ($card->getId()) {
				if ($card->getVendorId() != $vendor->getId()
				|| !in_array($deleteType, array(ZolagoOs_LoyaltyCard_Model_Card::DELETE_ONLY_CARD, ZolagoOs_LoyaltyCard_Model_Card::DELETE_WITH_SUBSCRIPTION))
				) {
					$this->_getSession()->addError($helper->__("Card does not exists"));
					return $this->_redirectReferer();
				}
			} else {
				$this->_getSession()->addError($helper->__("Card does not exists"));
				return $this->_redirectReferer();
			}

			/**
			 * @event loyalty_card_delete_before
			 * @event loyalty_card_delete_after
			 */
			$card->setDeleteType($deleteType);
			$card->delete();

			$this->_getSession()->addSuccess($helper->__("Card '%s' deleted", $card->getCardNumber()));

			return $this->_redirect("*/*");

		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			return $this->_redirectReferer();
		} catch (Exception $e) {
			$this->_getSession()->addError($helper->__("Some error occurred"));
			Mage::logException($e);
			return $this->_redirectReferer();
		}
	}

	/**
	 * @param $modelId
	 * @return ZolagoOs_LoyaltyCard_Model_Card
	 */
	protected function _initModel($modelId) {
		/* @var $model ZolagoOs_LoyaltyCard_Model_Card */
		$model = Mage::getModel("zosloyaltycard/card");
		if ($modelId) {
			$model->load($modelId);
		}
		Mage::register('current_loyalty_card', $model);
		return $model;
	}
}

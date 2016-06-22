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

		$this->_getSession()->setFormData(null);

		try {
			$card->addData($data);
			
			// Fix empty value
			if ($card->getId() == "") {
				$card->setId(null);
			}
			if ($card->isObjectNew()) {
				// Set Vendor Owner
				$card->setVendorId($vendor->getId());
				// Set operator
				$operatorId = null;
				if($this->_getSession()->isOperatorMode()) {
					$operatorId = $this->_getSession()->getOperator()->getId();
				}
				$card->setOperatorId($operatorId);
			}

			$card->save();
			
			$this->_getSession()->addSuccess($helper->__("Card '%s' saved", $card->getCardNumber()));
			
			return $this->_redirect("*/*/edit", array('id' => $card->getId()));
			
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

		return $this->_redirect("*/*");
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

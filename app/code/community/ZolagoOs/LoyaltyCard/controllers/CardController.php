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
		} elseif ($this->getRequest()->getParam('card_id', null) !== null) {
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

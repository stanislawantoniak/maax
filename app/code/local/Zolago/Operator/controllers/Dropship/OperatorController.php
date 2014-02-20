<?php

class Zolago_Operator_Dropship_OperatorController extends Zolago_Dropship_Controller_Vendor_Abstract {

	/**
	 * Operator listing action
	 */
	public function indexAction() {
		$this->_renderPage(null, 'zolagooperator');
	}

	/**
	 * Operator Edit
	 */
	public function editAction() {
		$operator = $this->_registerModel();
		$vendor = $this->_getSession()->getVendor();

		// Existing operator
		if ($operator->getId()) {
			if ($operator->getVendorId() != $vendor->getId()) {
				$this->_getSession()->addError(Mage::helper('zolagooperator')->__("Access denied"));				
				return $this->_redirect("*/*");
			}
		} else {
			if ($this->getRequest()->getParam('operator_id',null) !== null) {
				$this->_getSession()->addError(Mage::helper('zolagooperator')->__("Operator does not exists"));
				return $this->_redirect("*/*");
			}
		}
		// Process request & session data 
		$sessionData = $this->_getSession()->getFormData();
		if (!empty($sessionData)) {
			$operator->addData($sessionData);
			$this->_getSession()->setFormData(null);
		}

		$this->_renderPage(null,'zolagooperator');
	}

	/**
	 * New Pos
	 */
	public function newAction() {
		$this->_forward("edit");
	}

	/**
	 * Save Pos
	 */
	public function saveAction() {

		$helper = Mage::helper('zolagooperator');
		if (!$this->getRequest()->isPost()) {
			return $this->_redirectReferer();
		}
		// Form key valid?
		$formKey = Mage::getSingleton('core/session')->getFormKey();
		$formKeyPost = $this->getRequest()->getParam('form_key');
		if ($formKey != $formKeyPost) {
			return $this->_redirectReferer();
		}

		$operator = $this->_registerModel();
		$vendor = $this->_getSession()->getVendor();

		// Has premission?
		if ($operator->getId() && 
			($operator->getVendorId() !== $vendor->getId())) {
				$this->_getSession()->addError($helper->__("Access denied"));
				return $this->_redirectReferer();
		}

		// Try save
		$data = $this->getRequest()->getParams();
		
		// Check password and confirm
		if (isset($data['password']) && isset($data['confirmation']) && !empty($data['password']) && 
			$data['password'] !== $data['confirmation']) {
				$this->_getSession()->setFormData($data);
				$this->_getSession()->addError($helper->__("Password does not match the confirm password"));
				return $this->_redirectReferer();
		}
		
		$this->_getSession()->setFormData(null);
		$modelId = $this->getRequest()->getParam("operator_id");

		try {
			// Edit ?
			if (!empty($modelId) && !$operator->getId()) {
				throw new Mage_Core_Exception($helper->__("Operator not found"));
			}
			if(!empty($data['password'])){
				$operator->setPostPassword($data['password']);
				unset($data['password']);
				unset($data['confirmation']);
			}
			if(!isset($data['roles']) || !is_array($data['roles'])){
				$data['roles'] = array();
			}
			$operator->addData($data);
			$validErrors = $operator->validate();
			if ($validErrors === true) {
				// Fix empty value
				if($operator->getId()==""){
					$operator->setId(null);
				}
				// Add stuff for new operator
				if(!$operator->getId()) {
					// Set Vendor Owner
					$operator->setVendorId($vendor->getId());
				}
				$operator->save();
			} else {
				$this->_getSession()->setFormData($data);
				foreach ($validErrors as $error) {
					$this->_getSession()->addError($error);
				}
				return $this->_redirectReferer();
			}
			$this->_getSession()->addSuccess($helper->__("Operator Saved"));
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_getSession()->setFormData($data);
			return $this->_redirectReferer();
		} catch (Exception $e) {
			$this->_getSession()->addError($helper->__("Some error occure"));
			$this->_getSession()->setFormData($data);
			Mage::logException($e);
			return $this->_redirectReferer();
		}
		return $this->_redirect("*/*");
		
	}

	/**
	 * Register current model to use by blocks
	 * @return Zolago_Operator_Model_Operator
	 */
	protected function _registerModel() {
		$operatorId = $this->getRequest()->getParam("operator_id");
		$operator = Mage::getModel("zolagooperator/operator");
		if ($operatorId) {
			$operator->load($operatorId);
		}
		$operator->setPassword(null);
		Mage::register("current_operator", $operator);
		return $operator;
	}

}



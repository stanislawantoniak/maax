<?php

class Zolago_Sizetable_Dropship_SizetableController extends Zolago_Dropship_Controller_Vendor_Abstract {

	protected function _getSession()
	{
		return Mage::getSingleton('udropship/session');
	}

	/**
	 * Sizetables listing action
	 */
	public function indexAction() {
		$this->render();
	}

	public function editAction() {
		$this->_prepareSizetable(true);
		$this->render();
	}

	public function saveAction() {
		$session = $this->_getSession();
		try {
			if ($this->getRequest()->getPost()) {
				$data = $this->getRequest()->getParams();
				$formKey = Mage::getSingleton('core/session')->getFormKey();
				$formKeyPost = $this->getRequest()->getParam('form_key');
				if ($formKey != $formKeyPost) {
					return $this->_redirectReferer();
				} else {
					/** @var Zolago_Sizetable_Helper_Data $helper */
					$helper = Mage::helper('zolagosizetable');
					/** @var Zolago_Sizetable_Model_Sizetable $model */
					$model = Mage::getModel("zolagosizetable/sizetable");
					$modelId = $this->getRequest()->getParam("sizetable_id");
					if ($modelId !== null) {
						$model->load($modelId);
						if (!$model->getId()) {
							throw new Mage_Core_Exception($helper->__("Size table not found"));
						} elseif($model->getVendorId() != $this->_getSession()->getVendor()->getVendorId()) {
							return $this->_redirectReferer();
						}
					}
					$model->updateModelData($data);
					$model->setPostData($data['sizetable']);
					$model->save();
					$session->addSuccess($helper->__("Size table saved"));
				}
			}
		}catch(Mage_Core_Exception $e){
			$session->addError($e->getMessage());
			$session->setFormData($data);
			return $this->_redirectReferer();
		}catch(Exception $e){
			$session->addError($helper->__("Some error occured"));
			$session->setFormData($data);
			Mage::logException($e);
			return $this->_redirectReferer();
		}
		return $this->_redirect("*/*");
	}


	/*
	 *
	 *
	 * TODO: !!!!
	 *
	 *
	 *
	 *
	 */
	public function assignAction() {
		$session = $this->_getSession();
		try {
			if ($this->getRequest()->getPost()) {
				$data = $this->getRequest()->getParams();
				$formKey = Mage::getSingleton('core/session')->getFormKey();
				$formKeyPost = $this->getRequest()->getParam('form_key');
				if ($formKey != $formKeyPost) {
					return $this->_redirectReferer();
				} else {
					/** @var Zolago_Sizetable_Helper_Data $helper */
					$helper = Mage::helper('zolagosizetable');
					/** @var Zolago_Sizetable_Model_Sizetable $model */
					$model = Mage::getModel("zolagosizetable/sizetable");
					$modelId = $this->getRequest()->getParam("sizetable_id");
					if ($modelId !== null) {
						$model->load($modelId);
						if (!$model->getId()) {
							throw new Mage_Core_Exception($helper->__("Size table not found"));
						} elseif($model->getVendorId() != $this->_getSession()->getVendor()->getVendorId()) {
							return $this->_redirectReferer();
						}
					}
					$model->updateModelData($data);
					$model->setPostData($data['sizetable']);
					$model->save();
					$session->addSuccess($helper->__("Size table saved"));
				}
			}
		}catch(Mage_Core_Exception $e){
			$session->addError($e->getMessage());
			$session->setFormData($data);
			return $this->_redirectReferer();
		}catch(Exception $e){
			$session->addError($helper->__("Some error occured"));
			$session->setFormData($data);
			Mage::logException($e);
			return $this->_redirectReferer();
		}
		return $this->_redirect("*/*");
		/*Mage::log($this->getRequest()->getPost());
		$this->_redirectReferer();*/
	}

	protected function render() {
		$this->_renderPage(null,'zolagosizetable');
	}

	/**
	 * @param bool $addScopes
	 * @return bool|false|Mage_Core_Model_Abstract
	 */
	protected function _registerModel($addScopes = false) {
		$sizetableId = $this->getRequest()->getParam("sizetable_id");
		$sizetable = Mage::getModel("zolagosizetable/sizetable");
		if ($sizetableId) {
			$sizetable->load($sizetableId);
			if($addScopes)
				$sizetable->getScopes();
		} else {
			$sizetable = false;
		}
		Mage::register("sizetable", $sizetable);
		return $sizetable;
	}

	protected function _prepareSizetable($addScopes = false) {
		$sizetable = $this->_registerModel($addScopes);

		if($sizetable !== false) {
			$vendor = $this->_getSession()->getVendor();
			// Existing sizetable - has venor rights?
			if ($sizetable->getSizetableId() && $sizetable->getVendorId() != $vendor->getVendorId()) {
				$this->_getSession()->addError(Mage::helper('zolagosizetable')->__("You cannot edit this size table"));
				return $this->_redirect("*/*");
				// Sizetable id specified, but dons't exists
			} elseif (!$sizetable->getSizetableId() && $this->getRequest()->getParam("sizetable_id", null) !== null) {
				$this->_getSession()->addError(Mage::helper('zolagosizetable')->__("Size table doesn't exists"));
				return $this->_redirect("*/*");
			}
		}
	}
}
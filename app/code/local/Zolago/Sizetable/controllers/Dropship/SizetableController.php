<?php

class Zolago_Sizetable_Dropship_SizetableController extends Zolago_Dropship_Controller_Vendor_Abstract {

	/**
	 * Sizetables listing action
	 */
	public function indexAction() {
		$this->render();
	}

	public function editAction() {
		$this->render();
	}

	public function saveAction() {
		$session = Mage::getSingleton('udropship/session');
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

	public function assignAction() {
		Mage::log($this->getRequest()->getPost());
		$this->_redirectReferer();
	}

	protected function render() {
		$this->_renderPage(null,'zolagosizetable');
	}
}
<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Campaign_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction() {
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagocampaign');
    }
	
    public function editAction() {
		try{
			$this->_initModel();
		} catch (Mage_Core_Exception $ex) {
			$this->_getSession()->addError($ex->getMessage());
			return $this->_redirectReferer();
		}catch(Exception $ex){
			Mage::logException($ex);
			$this->_getSession()->addError("Some error occured");
			return $this->_redirectReferer();
		}
		$this->_renderPage(null, 'zolagocampaign');
    }
	
    public function newAction() {
       return $this->_forward('edit');
    }
	
	public function saveAction() {
		try{
			$model = $this->_initModel();
		} catch (Mage_Core_Exception $ex) {
			$this->_getSession()->addError($ex->getMessage());
		}catch(Exception $ex){
			Mage::logException($ex);
			$this->_getSession()->addError("Some error occured");
		}
		return $this->_redirectReferer();
	}
	
	public function validateKeyAction() {
		$key = $this->getRequest()->getParam('key');
		$store = Mage::app()->getStore();
		$collection = Mage::getResourceModel('core/url_rewrite_collection');
		/* @var $collection Mage_Core_Model_Resource_Url_Rewrite_Collection */
		$collection->addStoreFilter($store);
		$collection->addFieldToFilter("request_path", $key);
		
		$response = array("status"=>1, "content"=>$collection->getSize()==0);
		
		$this->getResponse()->
				setHeader('Content-type', 'application/json')->
				setBody(Mage::helper('core')->jsonEncode($response));
	}
	
	/**
	 * @return Zolago_Campaign_Model_Campaign
	 */
	protected function _initModel() {
		if(Mage::registry('current_campaign') instanceof Zolago_Campaign_Model_Campaign){
			return Mage::registry('current_campaign');
		}
		$modelId = (int)$this->getRequest()->getParam("id");
		$model = Mage::getModel("zolagocampaign/campaign");
		/* @var $model Zolago_Campaign_Model_Campaign */
		if($modelId){
			$model->load($modelId);
		}
		if(!$this->_validateModel($model)){
			throw new Mage_Core_Exception(Mage::helper('zolagocampaign')->__("Model is not vaild"));
		}
		Mage::register('current_campaign', $model);
		return $model;
	}
	
	/**
	 * @param Zolago_Campaign_Model_Campaign $model
	 * @return boolean
	 */
	protected function _validateModel(Zolago_Campaign_Model_Campaign $model) {
		if($model->isObjectNew()){
			return true;
		}
		$session = $this->_getSession();
		return $model->getVendorId()==$session->getVendorId();
	}
   
}

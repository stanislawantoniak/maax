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
        $campaign = $this->_initModel();
        $vendor = $this->_getSession()->getVendor();

        // Existing campaign
        if ($campaign->getId()) {
            if ($campaign->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif($this->getRequest()->getParam('campaign_id',null) !== null) {
            $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
            return $this->_redirect("*/*");
        }

        // Process request & session data
        $sessionData = $this->_getSession()->getFormData();
        if (!empty($sessionData)) {
            $campaign->addData($sessionData);
            $this->_getSession()->setFormData(null);
        }

		$this->_renderPage(null, 'zolagocampaign');
    }
	
    public function newAction() {
       return $this->_forward('edit');
    }
	
	public function saveAction() {
        $helper = Mage::helper('zolagocampaign');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }

        $campaign = $this->_initModel();
        $vendor = $this->_getSession()->getVendor();

        // Try save
        $data = $this->getRequest()->getParams();

        $this->_getSession()->setFormData(null);
        $modelId = $this->getRequest()->getParam("id");

        try {
            // If Edit
            if (!empty($modelId) && !$campaign->getId()) {
                throw new Mage_Core_Exception($helper->__("Campaign not found"));
            }

            $campaign->addData($data);
            $validErrors = $campaign->validate();
            if ($validErrors === true) {
                // Fix empty value
                if($campaign->getId()==""){
                    $campaign->setId(null);
                }
                // Add stuff for new campaign
                if(!$campaign->getId()) {
                    // Set Vendor Owner
                    $campaign->setVendorId($vendor->getId());
                }
                $campaign->save();
            } else {
                $this->_getSession()->setFormData($data);
                foreach ($validErrors as $error) {
                    $this->_getSession()->addError($error);
                }
                return $this->_redirectReferer();
            }
            $this->_getSession()->addSuccess($helper->__("Campaign Saved"));
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

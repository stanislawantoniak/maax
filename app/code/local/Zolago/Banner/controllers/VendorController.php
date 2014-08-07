<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Banner_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract
{
    public function indexAction()
    {
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagobanner');
    }

    public function editAction() {
        Mage::register('as_frontend', true);
        $banner = $this->_initModel();
        $vendor = $this->_getSession()->getVendor();

        $type = $this->getRequest()->getParam('type',null);

        // Existing banner
        if ($banner->getId()) {
            if ($banner->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagobanner')->__("Banner does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif($this->getRequest()->getParam('banner_id',null) !== null) {
            $this->_getSession()->addError(Mage::helper('zolagobanner')->__("Banner does not exists"));
            return $this->_redirect("*/*");
        }

        // Process request & session data
        $sessionData = $this->_getSession()->getFormData();
        if (!empty($sessionData)) {
            $banner->addData($sessionData);
            $this->_getSession()->setFormData(null);
        }

        $this->_renderPage(null, 'zolagobanner');
    }

    public function newAction() {
        return $this->_forward('type');
    }

    public function typeAction(){
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagobanner');
    }

    /**
     * @return Zolago_Banner_Model_Banner
     */
    protected function _initModel() {
        if(Mage::registry('current_banner') instanceof Zolago_Banner_Model_Banner){
            return Mage::registry('current_banner');
        }
        $modelId = (int)$this->getRequest()->getParam("id");
        $model = Mage::getModel("zolagobanner/banner");
        /* @var $model Zolago_Banner_Model_Banner */
        if($modelId){
            $model->load($modelId);
        }
        if(!$this->_validateModel($model)){
            throw new Mage_Core_Exception(Mage::helper('zolagobanner')->__("Model is not vaild"));
        }
        Mage::register('current_banner', $model);
        return $model;
    }

    /**
     * @param Zolago_Banner_Model_Banner $model
     * @return boolean
     */
    protected function _validateModel(Zolago_Banner_Model_Banner $model) {
        if($model->isObjectNew()){
            return true;
        }
        $session = $this->_getSession();
        return $model->getVendorId()==$session->getVendorId();
    }
}

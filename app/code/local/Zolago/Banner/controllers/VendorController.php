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

        // Existing banner
        if ($banner->getId()) {
            if ($banner->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagobanner')->__("Banner does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif($this->getRequest()->getParam('id',null) !== null) {
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
     * Redirect to edit form depends on banner type
     */
    public function setTypeAction()
    {
        $type = $this->getRequest()->getParam('type', null);
        if (empty($type)) {
            $this->_redirectUrl(Mage::helper('zolagobanner')->bannerTypeUrl());
        } else {
            $this->_redirectUrl(Mage::helper('zolagobanner')->bannerEditUrl($type));
        }

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

    public function saveAction(){
        $helper = Mage::helper('zolagobanner');

        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        $banner = $this->_initModel();
        $vendor = $this->_getSession()->getVendor();

        // Try save
        $data = $this->getRequest()->getParams();

        $this->_getSession()->setFormData(null);
        $modelId = $this->getRequest()->getParam("id");

        try {
            // If Edit
            if (!empty($modelId) && !$banner->getId()) {
                throw new Mage_Core_Exception($helper->__("Banner not found"));
            }

            $banner->addData($data);

            $validErrors = $banner->validate();
            if ($validErrors === true) {
                // Fix empty value
                if($banner->getId()==""){
                    $banner->setId(null);
                }
                // Add stuff for new banner
                if(!$banner->getId()) {
                    // Set Vendor Owner
                    $banner->setVendorId($vendor->getId());
                }
                $banner->save();

                //Save Banner Content
                Mage::log($data);
                Mage::log($_FILES);
                if(isset($_FILES['image'])) {
                    $images = $_FILES['image'];

                    $tmpName = $images['tmp_name'];
                    $name = $images['name'];

                    foreach($tmpName as $n => $imageName){
                        if(!empty($imageName)){
                            $path = Mage::getBaseDir() . "/media/banners/" . $name[$n];
                            Mage::log($path);
                            try {
                                move_uploaded_file($imageName, $path);
                            } catch (Exception $e) {
                                Mage::logException($e);
                            }
                        }

                    }

                }


            } else {
                $this->_getSession()->setFormData($data);
                foreach ($validErrors as $error) {
                    $this->_getSession()->addError($error);
                }
                return $this->_redirectReferer();
            }
            $this->_getSession()->addSuccess($helper->__("Banner Saved"));
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

//        foreach ($_FILES['slider']['tmp_name'] as $key => $file) {
//            move_uploaded_file($file["image"],
//                Mage::getBaseDir() . "/media/vendorSliders/" . $_FILES["slider"]["name"][$key]['image']);
//            echo "Stored in: " . "upload/" . $_FILES["slider"]["name"][$key]['image'];
//        }
        return $this->_redirect("*/*");
    }
}

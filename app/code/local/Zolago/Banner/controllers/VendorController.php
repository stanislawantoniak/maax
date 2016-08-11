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

        $bannerType = $this->getRequest()->getParam('type', "");

        /* @var $_zolagoDropshipHelper Zolago_Dropship_Helper_Data */
        $_zolagoDropshipHelper = Mage::helper("zolagodropship");

        //restrict for not LOCAL VENDOR
        /* @var $bannerTypeModel Zolago_Banner_Model_Banner_Type */
        $bannerTypeModel = Mage::getModel("zolagobanner/banner_type");
        $isAvailableType = $bannerTypeModel->isTypeDefinitionAvailableVorLocalVendor($bannerType);

        if(!$isAvailableType && !$_zolagoDropshipHelper->isLocalVendor()){
            //only Local vendor can edit Landing page Banners
            return $this->_redirect("campaign/vendor/index");
        }

        $campaignId = $this->getRequest()->getParam('campaign_id',null);
        $bannerId = $this->getRequest()->getParam('id',null);

        $banner = $this->_initModel($bannerId);
        $vendor = $this->_getSession()->getVendor();

        //validate vendor

        if(!empty($campaignId)){
            $modelCampaign = Mage::getModel("zolagocampaign/campaign");
            $modelCampaign->load($campaignId);
            if ($modelCampaign->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagobanner')->__("Campaign does not exists"));
                return $this->_redirect("campaign/vendor/index");
            }
        }

        // Existing banner
        if ($banner->getId()) {
            if ($banner->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagobanner')->__("Campaign creative does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif($this->getRequest()->getParam('id',null) !== null) {

            $this->_getSession()->addError(Mage::helper('zolagobanner')->__("Campaign creative does not exists"));
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
        $id = $this->getRequest()->getParam('campaign_id', 0);

        $campaign = Mage::getModel("zolagocampaign/campaign")->load($id);
        $vendor = $this->_getSession()->getVendor();

        // Existing campaign
        if ($campaign->getId()) {
            if ($campaign->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
                return $this->_redirect("campaign/vendor");
            }
        } elseif($this->getRequest()->getParam('campaign_id',null) !== null) {
            $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
            return $this->_redirect("campaign/vendor");
        }

        $this->_renderPage(null, 'zolagobanner');
    }

    /**
     * Redirect to edit form depends on banner type
     */
    public function setTypeAction()
    {
        $type = $this->getRequest()->getParam('type', null);
        $campaignId = $this->getRequest()->getParam("campaign_id");
        if (empty($type)) {
            $this->_redirectUrl(Mage::helper('zolagobanner')->bannerTypeUrl($campaignId));
        } else {
            $this->_redirectUrl(Mage::helper('zolagobanner')->bannerEditUrl($campaignId, $type));
        }

    }

    /**
     * @return Zolago_Banner_Model_Banner
     */
    protected function _initModel($modelId) {
        if(Mage::registry('current_banner') instanceof Zolago_Banner_Model_Banner){
            return Mage::registry('current_banner');
        }

        $model = Mage::getModel("zolagobanner/banner");
        /* @var $model Zolago_Banner_Model_Banner */
        if($modelId){
            $model->load($modelId);
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

        $modelId = $this->getRequest()->getParam("id");
        $banner = $this->_initModel($modelId);
        $vendor = $this->_getSession()->getVendor();

        // Try save
        $data = $this->getRequest()->getParams();

        $this->_getSession()->setFormData(null);


        try {
            // If Edit
            if (!empty($modelId) && !$banner->getId()) {
                throw new Mage_Core_Exception($helper->__("Campaign creative not found"));
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

                $bannerContentToSave = array();

                if($data['show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE){
                    if (isset($_FILES['image'])) {

                        $images = $_FILES['image'];

                        $tmpName = $images['tmp_name'];
                        $name = $images['name'];

                        foreach ($tmpName as $n => $imageName) {
                            if (!empty($imageName)) {
                                $uniqName = uniqid() . "_" . $name[$n];

                                $image = md5_file($imageName);
                                $image = md5(mt_rand() . $image);
                                $folder = $image[0] . "/" . $image[1] . "/" . $image[2] . "/";

                                @mkdir(Mage::getBaseDir() . "/media/banners/" . $folder, 0777, true);

                                $path = Mage::getBaseDir() . "/media/banners/" . $folder . $uniqName;
                                try {
                                    move_uploaded_file($imageName, $path);
                                } catch (Exception $e) {
                                    Mage::logException($e);
                                }
                                $bannerContentToSave['image'][$n]['path'] = "/banners/" . $folder . $uniqName;
                            } elseif (isset($data['image']) && !empty($data['image'])) {
                                $bannerContentToSave['image'][$n]['path'] = isset($data['image'][$n]) ? $data['image'][$n]['value'] : '';
                            }

                            // Only Inspiration need to be resize ( box and sliders not )
                            $type = $banner->getType();
                            if (Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION == $type) {
                                $_path = $bannerContentToSave['image'][$n]['path'];
                                $imageBoxPath = Mage::getBaseDir('media') . $_path;
                                $imageBoxResizePath = Mage::getBaseDir('media') . DS . Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner::getImageResizePath($banner->getType()) . $_path;
                                Mage::getModel("zolagobanner/banner")->scaleImage(
                                    $imageBoxPath,
                                    $imageBoxResizePath,
                                    Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner::BANNER_INSPIRATION_WIDTH,
                                    Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner::BANNER_INSPIRATION_HEIGHT);
                            }
                        }
                        unset($n);
                    }
                    foreach($data as $i => $dataItem){
                        $bannerContentToSave['banner_id'] = $banner->getId();
                        $bannerContentToSave['show'] = $data['show'];
                        $bannerContentToSave['html'] = '';

                        if ($i == 'image_url') {
                            foreach ($dataItem as $n => $imageUrl) {
                                $bannerContentToSave['image'][$n]['url'] = $imageUrl;
                            }
                            unset($n);
                        }

                        if ($i == 'caption_url') {
                            foreach ($dataItem as $n => $captionUrl) {
                                $bannerContentToSave['caption'][$n]['url'] = $captionUrl;
                            }
                            unset($n);
                        }
                        if ($i == 'caption_text') {
                            foreach ($dataItem as $n => $captionText) {
                                $bannerContentToSave['caption'][$n]['text'] = $captionText;
                            }
                            unset($n);
                        }
                    }
                }
                if ($data['show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML) {
                    $bannerContentToSave['banner_id'] = $banner->getId();
                    $bannerContentToSave['show'] = $data['show'];
                    $bannerContentToSave['html'] = $data['banner_html'];
                }

                Mage::getModel('zolagobanner/banner')->saveBannerContent($bannerContentToSave);


            } else {
                $this->_getSession()->setFormData($data);
                foreach ($validErrors as $error) {
                    $this->_getSession()->addError($error);
                }
                return $this->_redirectReferer();
            }
            $this->_getSession()->addSuccess($helper->__("Campaign creative saved"));
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
        return $this->_redirect("campaign/vendor/edit/", array('id' => $data['campaign_id'], '_fragment' => 'tab_banners'));
    }


}

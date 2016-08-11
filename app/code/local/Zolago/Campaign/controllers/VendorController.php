<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Campaign_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract
{


    public function indexAction()
    {
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagocampaign');
    }

    public function editAction()
    {
        Mage::register('as_frontend', true);
        $id = $this->getRequest()->getParam('id');
        $campaign = $this->_initModel($id);
        $vendor = $this->_getSession()->getVendor();

        // Existing campaign
        if ($campaign->getId()) {
            if ($campaign->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif ($this->getRequest()->getParam('campaign_id', null) !== null) {
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

    public function newAction()
    {
        return $this->_forward('edit');
    }


    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function saveProductsAction()
    {

        $campaignId = $this->getRequest()->getParam('id', null);
        $productsStr = $this->getRequest()->getParam('products', array());

        $campaign = $this->_initModel($campaignId);
        $vendor = $this->_getSession()->getVendor();

        /* @var $udropshipHelper ZolagoOs_OmniChannel_Helper_Data */
        $udropshipHelper = Mage::helper("udropship");
        $localVendor = $udropshipHelper->getLocalVendorId();

        // Existing campaign
        if ($campaign->getId()) {
            if ($campaign->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif ($this->getRequest()->getParam('id', null) !== null) {
            $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
            return $this->_redirect("*/*");
        }
        $skuVS = array();
        if (is_string($productsStr)) {
            $skuVS = array_map('trim', explode(",", $productsStr));
        }

        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('skuv', array('in' => $skuVS));
        if ($vendor->getId() !== $localVendor) {
            $collection->addAttributeToFilter('udropship_vendor', $vendor->getId());
        }
        $collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collection = $collection->getAllIds();

        $productIds = array();
        if (!empty($collection)) {
            foreach ($collection as $productId) {
                $productIds[] = $productId;
            }
        }

        if($productIds){
            /* @var $model Zolago_Campaign_Model_Resource_Campaign */
            $model = Mage::getResourceModel("zolagocampaign/campaign");
            $model->saveProducts($campaignId, $productIds);
        }
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function productsAction()
    {

        $campaignId = $this->getRequest()->getParam('id', null);

        $isAjax = $this->getRequest()->getParam('isAjax', false);
        $campaign = $this->_initModel($campaignId);
        $vendor = $this->_getSession()->getVendor();

        // Existing campaign
        if ($campaign->getId()) {
            if ($campaign->getVendorId() != $vendor->getId()) {
                $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
                return $this->_redirect("*/*");
            }
        } elseif ($this->getRequest()->getParam('id', null) !== null) {
            $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Campaign does not exists"));
            return $this->_redirect("*/*");
        }


        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('zolagocampaign/vendor_campaign_product_grid')
                ->toHtml()
        );

        if (!$isAjax) {
            return $this->_redirectReferer();
        }
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function saveAction()
    {
        $helper = Mage::helper('zolagocampaign');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        // Form key valid?
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $formKeyPost = $this->getRequest()->getParam('form_key');
        if ($formKey != $formKeyPost) {
            return $this->_redirectReferer();
        }
        $id = $this->getRequest()->getPost('campaign_id');
        $campaign = $this->_initModel($id);


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
                if ($campaign->getId() == "") {
                    $campaign->setId(null);
                }

                // Add stuff for new campaign
                if (!$campaign->getId()) {
                    // Set Vendor Owner
                    $campaign->setVendorId($vendor->getId());
                }

                //Save Coupon image and Coupon PDF
                /* @var $campaignFormHelper Zolago_Campaign_Helper_Form */
                $campaignFormHelper = Mage::helper("zolagocampaign/form");

                if (isset($_FILES['coupon_image'])) {
                    $couponImage = $_FILES["coupon_image"];
                    $couponImageTmpName = $couponImage["tmp_name"];
                    $couponImageName = $couponImage["name"];


                    if (!empty($couponImageName)) { //if file just uploaded
                        $couponImagePath = $campaignFormHelper->saveFormImage($couponImageName, $couponImageTmpName, Zolago_Campaign_Model_Campaign::LP_COUPON_IMAGE_FOLDER);
                        $campaign->setData("coupon_image", $couponImagePath);
                    } elseif (isset($data['coupon_image']) && !empty($data['coupon_image'])) {
                        $campaign->setData("coupon_image", $data['coupon_image']['value']);
                    }


                }
                if (isset($_FILES['coupon_conditions'])) {
                    $couponConditions = $_FILES["coupon_conditions"];
                    $couponConditionsTmpName = $couponConditions["tmp_name"];
                    $couponConditionsName = $couponConditions["name"];
                    if (!empty($couponConditionsName)) {
                        $couponConditionsPath = $campaignFormHelper->saveFormImage($couponConditionsName, $couponConditionsTmpName, Zolago_Campaign_Model_Campaign::LP_COUPON_PDF_FOLDER);
                        $campaign->setData("coupon_conditions", $couponConditionsPath);
                    } elseif (isset($data['coupon_conditions']) && !empty($data['coupon_conditions'])) {
                        $campaign->setData("coupon_conditions", $data['coupon_conditions']['value']);
                        //check if coupon_conditions file should be removed
                        if (isset($data['remove_coupon_conditions'])) {
                            $campaign->setData("coupon_conditions", "");
                            @unlink(Mage::getBaseDir("media") . DS . Zolago_Campaign_Model_Campaign::LP_COUPON_PDF_FOLDER . DS . $data['coupon_conditions']['value']);
                        }
                    }
                }
                //--Save Coupon image and Coupon PDF

                $campaign->save();

                /**
                 * @see Zolago_Campaign_Model_Observer::campaignAfterUpdate
                 */
                Mage::dispatchEvent(
                    "campaign_save_after",
                    array(
                        "campaign" => $campaign
                    )
                );
            } else {
                $this->_getSession()->setFormData($data);
                foreach ($validErrors as $error) {
                    $this->_getSession()->addError($error);
                }
                return $this->_redirectReferer();
            }
            if ($campaign->isObjectNew()) {
                $this->_getSession()->addSuccess($helper->__('Campaign "%s" saved. Now you can attach creations and products to the campaign.', $campaign->getName()));
            } else {
                $this->_getSession()->addSuccess($helper->__('Campaign "%s" saved', $campaign->getName()));
            }

            $campaignId = $campaign->getId();
            if ($campaign->isObjectNew() && !empty($campaignId)) {
                return $this->_redirect("*/*/edit", array('id' => $campaignId));
            }
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
     * Ajax action
     * @return Mage_Core_Controller_Varien_Action
     */
    public function removeProductAction()
    {
        $campaignId = $this->getRequest()->getParam("campaignId");
        $productId = $this->getRequest()->getParam("id");

        if (!empty($campaignId) && !empty($productId)) {
            /* @var $model Zolago_Campaign_Model_Resource_Campaign */
            $model = Mage::getResourceModel("zolagocampaign/campaign");
            $model->removeProduct($campaignId, $productId);
        }
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function removeBannerAction()
    {
        $campaignId = $this->getRequest()->getParam("campaignId");
        $bannerId = $this->getRequest()->getParam("id");

        if (!empty($campaignId) && !empty($bannerId)) {

            /* @var $modelPlacement Zolago_Campaign_Model_Resource_Placement */
            $modelPlacement = Mage::getResourceModel("zolagocampaign/placement");
            $modelPlacement->removeBanner($campaignId, $bannerId);
        }
        return $this->_redirectReferer();
    }


    public function validateKeyAction()
    {
        $key = $this->getRequest()->getParam('key');
        $store = Mage::app()->getStore();
        $collection = Mage::getResourceModel('core/url_rewrite_collection');
        /* @var $collection Mage_Core_Model_Resource_Url_Rewrite_Collection */
        $collection->addStoreFilter($store);
        $collection->addFieldToFilter("request_path", $key);

        $response = array("status" => 1, "content" => $collection->getSize() == 0);

        $this->getResponse()->
        setHeader('Content-type', 'application/json')->
        setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * @return Zolago_Campaign_Model_Campaign
     */
    protected function _initModel($modelId)
    {
        if (Mage::registry('current_campaign') instanceof Zolago_Campaign_Model_Campaign) {
            return Mage::registry('current_campaign');
        }

        $model = Mage::getModel("zolagocampaign/campaign");
        /* @var $model Zolago_Campaign_Model_Campaign */
        if ($modelId) {
            $model->load($modelId);
        }

        Mage::register('current_campaign', $model);
        return $model;
    }

    /**
     * @param Zolago_Campaign_Model_Campaign $model
     * @return boolean
     */
    protected function _validateModel(Zolago_Campaign_Model_Campaign $model)
    {
        if ($model->isObjectNew()) {
            return true;
        }
        $session = $this->_getSession();
        return $model->getVendorId() == $session->getVendorId();
    }

    /**
     *
     */
    public function getCampaignDataAction()
    {
        $campaignData = array();
        $modelId = (int)$this->getRequest()->getParam("id");
        $model = Mage::getModel("zolagocampaign/campaign");
        $campaign = $model->load($modelId);

        if (!empty($campaign)) {
            $vendorsList = Mage::helper('zolagocampaign')->getAllVendorsList();
            $format = "d.m.Y H:i:s";
            $dateFrom = $campaign->getData('date_from');
            $dateTo = $campaign->getData('date_to');

            $vendor = Mage::getSingleton('udropship/session')->getVendor();
            $vendorId = $vendor->getId();

            $campaignData = array(
                'campaign_id' => $campaign->getId(),
                'vendor_id' => $campaign->getVendorId(),
                'showedit' => (int)($campaign->getVendorId() == $vendorId),
                'campaign_vendor' => $vendorsList[$campaign->getVendorId()],
                'date_from' => !empty($dateFrom) ? date($format, strtotime($dateFrom)) : '',
                'date_to' => !empty($dateTo) ? date($format, strtotime($dateTo)) : ''
            );

            $showEditLink = ($campaign->getVendorId() == $vendorId);

            $status = array();
            //status
            $bannersConfiguration = Mage::helper('zolagobanner')->getBannersConfiguration();

            /* @var $statuses Zolago_Campaign_Model_Campaign_PlacementStatus */
            $statuses = Mage::getSingleton('zolagocampaign/campaign_PlacementStatus')
                ->statusOptionsData($campaign->getId(), $showEditLink);

            $now = Mage::getModel('core/date')->timestamp(time());
            if (!empty($dateTo) && !empty($dateFrom)) {

                //1.Expired
                if (strtotime($dateFrom) < $now && $now < strtotime($dateTo)) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_ACTIVE];
                }
                if ($now < strtotime($dateFrom)) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_FUTURE];
                }
                $h = !empty($bannersConfiguration->campaign_expires) ? $bannersConfiguration->campaign_expires : 48;

                if (strtotime($dateTo) >= $now && strtotime($dateTo) < ($now + $h * 3600)) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_EXPIRES_SOON];
                }

                if (strtotime($dateTo) < $now) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_EXPIRED];
                }
            } elseif (empty($dateTo) && !empty($dateFrom)) {
                if (strtotime($dateFrom) < $now) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_ACTIVE];
                }
                if ($now < strtotime($dateFrom)) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_FUTURE];
                }
            } elseif (!empty($dateTo) && empty($dateFrom)) {
                if ($now < strtotime($dateTo)) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_ACTIVE];
                }

                $h = !empty($bannersConfiguration->campaign_expires) ? $bannersConfiguration->campaign_expires : 48;

                if (strtotime($dateTo) >= $now && strtotime($dateTo) < ($now + $h * 3600)) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_EXPIRES_SOON];
                }

                if (strtotime($dateTo) < $now) {
                    $status = $statuses[Zolago_Campaign_Model_Campaign_PlacementStatus::TYPE_EXPIRED];
                }
            }

            $campaignData['status'] = $status;
        }
        echo Mage::helper('core')->jsonEncode($campaignData);
    }

    /**
     * @return array
     */
    public function getBannerPreviewImageAction()
    {
        $id = (int)$this->getRequest()->getParam("id");
        //preview image
        $bannersConfiguration = Mage::helper('zolagobanner')->getBannersConfiguration();
        $previewImage = $bannersConfiguration->no_image;
        $previewImageHtml = $bannersConfiguration->image_html;


        /* @var $campaignPlacementModel Zolago_Campaign_Model_Resource_Placement */
        $campaignPlacementModel = Mage::getResourceModel("zolagocampaign/placement");

        $bannerShow = $campaignPlacementModel->getBannerImageData($id);

        if (!empty($bannerShow)) {
            if ($bannerShow['show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE) {
                $placementImage = unserialize($bannerShow['image']);
                if (!empty($placementImage)) {
                    $placementImage = reset($placementImage);
                    $previewImage = Mage::getBaseUrl('media') . $placementImage['path'];
                }
            }


            if ($bannerShow['show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML) {
                $previewImage = $previewImageHtml;
            }
        }
        echo $previewImage;
    }


    public function get_category_treeAction() {
        $vendor = (int)$this->getRequest()->getParam("vendor", 0);
        $website = (int)$this->getRequest()->getParam("website", 0);
        $tree = Mage::helper("zolagocampaign")->getCategoriesTree($vendor, $website);

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/text', true);

        $this->getResponse()->setBody($tree);
    }
}

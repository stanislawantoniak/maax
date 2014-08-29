<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Campaign_Placement_CategoryController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
        $helper = Mage::helper('zolagocampaign');
        $categoryId = $this->getRequest()->getParam('category', null);

        if (empty($categoryId)) {
            $this->_getSession()->addError($helper->__("Category not found"));
            return $this->_redirect("campaign/placement/index");
        }
        $categoryObj = Mage::getModel('catalog/category')->load($categoryId);
        $category = $categoryObj->getId();
        if (empty($category)) {
            $this->_getSession()->addError($helper->__("Category not found"));
            return $this->_redirect("campaign/placement/index");
        }
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagocampaign');
    }

    public function saveAction()
    {
        $helper = Mage::helper('zolagocampaign');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }

        $data = $this->getRequest()->getParams();

        $categoryId = (int)$data['category'];


        if(empty($categoryId)){
            $this->_getSession()->addError($helper->__("Category does not exist"));
            //return $this->_redirect("campaign/placement/index");
        }
        $categoryObj = Mage::getModel('catalog/category')->load($categoryId);
        if(empty($categoryObj)){
            $this->_getSession()->addError($helper->__("Category does not exist"));
            //return $this->_redirect("campaign/placement/index");
        }
        $campaign = Mage::getResourceModel('zolagocampaign/campaign');
        //remove items
        if(isset($data['remove'])){
            try {
                $campaign->removeCampaignPlacements($data['remove']);
            } catch (Exception $e) {
                echo $helper->__("Some error occure");
                $this->_getSession()->addError($helper->__("Some error occure"));
                Mage::logException($e);
                //return $this->_redirect("campaign/placement/index");
            }
        }
        echo 'removed';
        $placements = array();
        if (!empty($data)) {
            $itemsCount = count($data['campaign_id']);
            $vendorId = Mage::getSingleton('udropship/session')->getVendor()->getId();
            for ($i = 0; $i < $itemsCount; $i++) {
                //campaigns
                $placements[] = array(
                    'vendor_id' => $vendorId,
                    'category_id' => $data['category'],
                    'campaign_id' => $data['campaign_id'][$i],
                    'banner_id' => $data['banner_id'][$i],
                    'type' => $data['type'][$i],
                    'position' => $data['position'][$i],
                    'priority' => $data['priority'][$i],
                );
            }
        }
        if (!empty($placements)) {
            try {
                $campaign->setCampaignPlacements($categoryId, $placements);
            } catch (Exception $e) {
                echo $helper->__("Some error occure");
                $this->_getSession()->addError($helper->__("Some error occure"));
                Mage::logException($e);
                //return $this->_redirect("campaign/placement/index");
            }

        }
        //return $this->_redirect("campaign/placement/index");
    }

    public function getCampaignCreationsAction()
    {
        $campaign = $this->getRequest()->getParam('campaign', null);
        if (empty($campaign)) {
            return Mage::helper('core')->jsonEncode(null);
        }
        $model = Mage::getResourceModel('zolagobanner/banner');
        $banners = $model->getCampaignBanners($campaign);

        $bannersOptions = array();
        if (!empty($banners)) {
            foreach ($banners as $banner) {
                $bannersOptions[$banner['banner_id']] = array('banner_id' => $banner['banner_id'], 'name' => $banner['name']);
            }
        }

        echo Mage::helper('core')->jsonEncode($bannersOptions);
    }

}

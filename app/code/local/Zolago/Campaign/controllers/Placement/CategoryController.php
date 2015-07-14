<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Campaign_Placement_CategoryController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
        //restrict category access
        $noAccess = true;
        $category = $this->getRequest()->getParam('category', 0);

        $vendorCategories = Mage::helper('zolagocampaign')
            ->getVendorCategoriesList();

        if (!empty($vendorCategories)) {
            $vendorCats = array();
            foreach ($vendorCategories as $vendorCategory) {
                $vendorCats[$vendorCategory['id']] = $vendorCategory['id'];
            }
            if (in_array($category, $vendorCats)) {
                $noAccess = false;
            }
        }

        if ($noAccess) {
            return $this->_redirect('campaign/placement/index');
        }
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagocampaign');
    }

    public function saveNewAction() {
        $helper = Mage::helper('zolagocampaign');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        $data = $this->getRequest()->getParams();

        $placement = array(
            'vendor_id' => $data['vendor_id'],
            'category_id' => $data['category_id'],
            'campaign_id' => $data['campaign_id'],
            'banner_id' => $data['banner_id'],
            'type' => $data['type'],
            'position' => $data['position'],
            'priority' => $data['priority'],
        );
        $campaign = Mage::getResourceModel('zolagocampaign/campaign');
        try {
            $id = $campaign->setNewCampaignPlacement($placement);
            echo $id;
        } catch (Exception $e) {
            echo $helper->__("Some error occure");
        }
    }
    public function saveAction()
    {
        $helper = Mage::helper('zolagocampaign');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }

        $data = $this->getRequest()->getParams();

        $categoryId = (int)$data['category'];
        $campaign = Mage::getResourceModel('zolagocampaign/campaign');
        //remove items
        if(isset($data['remove'])){
            try {
                $campaign->removeCampaignPlacements($data['remove']);
            } catch (Exception $e) {
                echo $helper->__("Some error occurred");
            }
        }
        $placements = array();
        if (!empty($data)) {
            $itemsCount = count($data['campaign_id']);
            $vendorId = Mage::getSingleton('udropship/session')->getVendor()->getId();
            for ($i = 0; $i < $itemsCount; $i++) {
                //campaigns
                $placements[] = array(
                    'placement_id' => $data['placement_id'][$i],
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
                $campaign->setCampaignPlacements($placements);
            } catch (Exception $e) {
                echo $helper->__("Some error occurred");
            }

        }
    }

    public function getCampaignCreationsAction()
    {
        $campaign = $this->getRequest()->getParam('campaign', null);
        $bannerType = $this->getRequest()->getParam('type', null);
        if (empty($campaign)) {
            return Mage::helper('core')->jsonEncode(null);
        }
        $model = Mage::getResourceModel('zolagobanner/banner');
        $banners = $model->getCampaignBanners($campaign, $bannerType);

        $bannersOptions = array();
        if (!empty($banners)) {
            foreach ($banners as $banner) {
                $bannersOptions[$banner['banner_id']] = array('banner_id' => $banner['banner_id'], 'name' => $banner['name']);
            }
        }

        echo Mage::helper('core')->jsonEncode($bannersOptions);
    }

}

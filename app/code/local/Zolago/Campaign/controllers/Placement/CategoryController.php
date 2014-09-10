<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Campaign_Placement_CategoryController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
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

        echo $categoryId;
        $campaign = Mage::getResourceModel('zolagocampaign/campaign');
        //remove items
        if(isset($data['remove'])){
            try {
                $campaign->removeCampaignPlacements($data['remove']);
            } catch (Exception $e) {
                echo $helper->__("Some error occure");
            }
        }
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
                $campaign->setCampaignPlacements($categoryId, $vendorId, $placements);
            } catch (Exception $e) {
                echo $helper->__("Some error occure");
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

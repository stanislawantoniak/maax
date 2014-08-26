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

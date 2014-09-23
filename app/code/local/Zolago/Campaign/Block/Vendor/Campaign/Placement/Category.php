<?php

class Zolago_Campaign_Block_Vendor_Campaign_Placement_Category extends Mage_Core_Block_Template
{

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * @return Unirgy_Dropship_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }

    public function getCampaigns(){
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendorId = $vendor->getId();

        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $campaign = Mage::getResourceModel("zolagocampaign/campaign");
        $campaignBank = $campaign->getCampaigns();
        $result = array();

        $campaigns = array();


        //reformat result
        if ($vendorId == Mage::helper('udropship')->getLocalVendorId()) {
            //prepare campaigns group by type and vendor
            foreach($campaignBank as $campaign){
                $campaigns[$campaign["banner_type"]][$campaign['vendor_id']][$campaign['campaign_id']] = array(
                    'campaign_id' => $campaign['campaign_id'],
                    'vendor_id' => $campaign['vendor_id'],
                    'name' => $campaign['name'],
                    'date_from' => !empty($campaign['date_from']) ? date("d.m.Y H:i:s",strtotime($campaign['date_from'])) : '',
                    'date_to' => !empty($campaign['date_to']) ? date("d.m.Y H:i:s",strtotime($campaign['date_to'])) : ''
                );
            }
            $vendorModel = Mage::getModel('udropship/vendor');
            $vendorCollection = $vendorModel->getCollection();
            $vendorsList = array();
            foreach($vendorCollection as $vendorObj){
                $vendorsList[$vendorObj->getId()] = $vendorObj->getVendorName();
            }

            //group by vendor
            if (!empty($campaigns)) {
                foreach ($campaigns as $type => $vendorItems) {
                    foreach ($vendorItems as $vendor => $_) {
                        $result[$type][$vendorsList[$vendor]] = array_values($_);
                    }
                }
            }
        } else {
            //prepare campaigns group by type
            foreach($campaignBank as $campaign){
                $campaigns[$campaign["banner_type"]][$campaign['campaign_id']] = array(
                    'campaign_id' => $campaign['campaign_id'],
                    'vendor_id' => $campaign['vendor_id'],
                    'name' => $campaign['name'],
                    'date_from' => !empty($campaign['date_from']) ? date("d.m.Y H:i:s",strtotime($campaign['date_from'])) : '',
                    'date_to' => !empty($campaign['date_to']) ? date("d.m.Y H:i:s",strtotime($campaign['date_to'])) : ''
                );
            }

            if (!empty($campaigns)) {
                foreach ($campaigns as $type => $_) {
                    $result[$type] = array_values($_);
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getCategoryPlacements()
    {
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendorId = $vendor->getId();

        $categoryId = $this->getCategoryId();
        $campaign = Mage::getResourceModel("zolagocampaign/campaign");
        $placements = $campaign->getCategoryPlacements($categoryId, $vendorId);

        $placementsByType = array();
        if (!empty($placements)) {
            $bannersConfiguration = Mage::helper('zolagobanner')->getBannersConfiguration();

            foreach ($placements as $placement) {
                $dateFrom = $placement['campaign_date_from'];
                $dateTo = $placement['campaign_date_to'];
                $placement['campaign_date_from'] = !empty($dateFrom) ? date("d.m.Y H:i:s", strtotime($dateFrom)) : '';
                $placement['campaign_date_to'] = !empty($dateTo) ? date("d.m.Y H:i:s", strtotime($dateTo)) : '';
                //preview image
                $placement['preview_image'] = $bannersConfiguration->no_image;

                if($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE){
                    $placementImage = unserialize($placement['banner_image']);

                    if(!empty($placementImage)){
                        $firstImage = reset($placementImage);
                        $placement['preview_image'] = Mage::getBaseUrl('media').$firstImage['path'];
                    }
                }
                if($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML){
                    $placement['preview_image'] = $bannersConfiguration->image_html;
                }
                $status = array();
                //status
                if(!empty($dateTo) && !empty($dateFrom)){
                    $statuses = Mage::getSingleton('zolagocampaign/campaign_PlacementStatus')->toOptionArray();
                    //Zend_Debug::dump($statuses);
                    //1.Expired
                    $now = Mage::getModel('core/date')->timestamp(time());

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
                }

                $placement['status'] = $status;


                $placementsByType[$placement['type']][] = $placement;
            }
        }

        return $placementsByType;
    }

    public function getCategoryId()
    {
        $category = $this->getRequest()->getParam('category', null);
        return $category;
    }

    public function getCategoryName()
    {
        $categoryName = '';
        $category = $this->getRequest()->getParam('category', null);
        if (!empty($category)) {
            $categoryModel = Mage::getModel('catalog/category');
            $categoryObj = $categoryModel->load($category);
            $categoryName = $categoryObj->getName();
        } else {
            $categoryName = Mage::helper('zolagocampaign') -> __('Vendor landing page');
        }
        return $categoryName;
    }
}
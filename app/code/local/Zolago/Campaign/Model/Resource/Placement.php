<?php

/**
 * Class Zolago_Campaign_Model_Resource_Placement
 */
class Zolago_Campaign_Model_Resource_Placement extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocampaign/campaign_placement', "placement_id");
    }


    /**
     * @param $campaignId
     * @param $bannerId
     */
    public function removeBanner($campaignId, $bannerId)
    {
        $table = $this->getTable("zolagobanner/banner");
        $where = "campaign_id={$campaignId} AND banner_id={$bannerId}";
        $this->_getWriteAdapter()->delete($table, $where);
    }


    /**
     * @param $placement
     * @return mixed
     */
    public function setNewCampaignPlacement($placement){
        $table = $this->getTable("zolagocampaign/campaign_placement");

        $vendor_id = $placement['vendor_id'];
        $category_id = $placement['category_id'];
        $campaign_id = $placement['campaign_id'];
        $banner_id = $placement['banner_id'];
        $type = $placement['type'];
        $position = $placement['position'];
        $priority = $placement['priority'];

        $sql = "INSERT INTO {$table} (vendor_id,category_id,campaign_id,banner_id,type,position,priority)
        VALUES ({$vendor_id},{$category_id},{$campaign_id},{$banner_id},'{$type}',{$position},{$priority})";

        $this->_getWriteAdapter()->query($sql);
        $lastInsertId = $this->_getWriteAdapter()->lastInsertId();

        return $lastInsertId;
    }

    /**
     * @param $categoryId
     * @param array $placements
     * @return $this
     */
    public function setCampaignPlacements(array $placements)
    {
        $table = $this->getTable("zolagocampaign/campaign_placement");

        if (count($placements)) {

            $this->_getWriteAdapter()
                ->insertOnDuplicate($table, $placements, array('position', 'priority'));

        }
        return $this;
    }

    /**
     * @param array $placements
     * @return $this
     */
    public function removeCampaignPlacements(array $placements)
    {
        $table = $this->getTable("zolagocampaign/campaign_placement");
        foreach($placements as $placement){
            $where = $this->getReadConnection()
                ->quoteInto("placement_id=?", $placement);
            $this->_getWriteAdapter()->delete($table, $where);
        }
        return $this;
    }


    /**
     * @param $categoryId
     * @param $vendorId
     * @param array $bannerTypes
     * @param bool|FALSE $notExpired
     * @param $websiteId
     * @return array
     */
    public function getCategoryPlacements($categoryId, $vendorId, $bannerTypes = array(), $notExpired = FALSE, $websiteId)
    {
        $table = $this->getTable("zolagocampaign/campaign_placement");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign_placement" => $table), array("*"));
        $select->joinLeft(
            array('campaign' => 'zolago_campaign'),
            'campaign.campaign_id=campaign_placement.campaign_id',
            array(
                'campaign_name' => 'campaign.name',
                'campaign_date_from' => 'campaign.date_from',
                'campaign_date_to' => 'campaign.date_to',
                'campaign_status' => 'campaign.status',
                'campaign_vendor' => 'campaign.vendor_id',
                'campaign_url' => 'campaign.campaign_url',
            )
        );
        $select->joinLeft(
            array('banner' => 'zolago_banner'),
            'banner.banner_id=campaign_placement.banner_id',
            array(
                'banner_name' => 'banner.name'
            )
        );
        $select->joinLeft(
            array('banner_content' => 'zolago_banner_content'),
            'banner.banner_id=banner_content.banner_id',
            array(
                'banner_show' => 'banner_content.show',
                'banner_html' => 'banner_content.html',
                'banner_image' => 'banner_content.image',
                'banner_caption' => 'banner_content.caption'
            )
        );

        $select->joinLeft(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign_website.campaign_id=campaign.campaign_id',
            array("campaign_website" => "campaign_website.website_id")
        );


        $select->where("campaign_placement.category_id=?", $categoryId);
        //$select->where("campaign.vendor_id=campaign_placement.vendor_id");
        $select->where("campaign_placement.vendor_id=?", $vendorId);
        if(!empty($bannerTypes)){
            $select->where("banner.type in(?)", $bannerTypes);
        }


        $select->where("campaign_website.website_id IN(?)", $websiteId);


        if($notExpired){
            $endYTime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));

            $select->where("campaign.date_to >= '{$endYTime}'");
        }
        $select->order("banner.type DESC");
        $select->order("campaign_placement.priority ASC");

        return $this->getReadConnection()->fetchAssoc($select);
    }


    /**
     * @param $bannerId
     * @return array
     */
    public function getBannerImageData($bannerId)
    {
        $table = $this->getTable("zolagobanner/banner_content");
        $select = $this->getReadConnection()->select();
        $select->from(array("banner_content" => $table), array("*"));

        $select->where("banner_content.banner_id=?", $bannerId);
        return $this->getReadConnection()->fetchRow($select);
    }

}
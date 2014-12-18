<?php

class Zolago_Campaign_Model_Resource_Campaign extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocampaign/campaign', "campaign_id");
    }

    /**
     * @param Mage_Core_Model_Abstract $param
     * @return Zolago_Operator_Model_Resource_Operator
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        // Website Assignment
        if ($object->hasData("website_ids")) {
            $this->_setWebsites($object, $object->getData("website_ids"));
        }
        return parent::_afterSave($object);
    }

    /**
     * @param $campaignId
     * @param $productId
     */
    public function removeProduct($campaignId,$productId){
        $table = $this->getTable("zolagocampaign/campaign_product");

        $where = "campaign_id={$campaignId} AND product_id={$productId}";
        echo $where;
        $this->_getWriteAdapter()->delete($table, $where);
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

    public  function saveProducts($campaignId, array $productIds)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $where = $this->getReadConnection()
            ->quoteInto("campaign_id=?", $campaignId);
        $this->_getWriteAdapter()->delete($table, $where);

        $toInsert = array();

        foreach ($productIds as $productId) {
            $toInsert[] = array("campaign_id" => $campaignId, "product_id" => $productId);
        }
        if (count($toInsert)) {
            $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
        }
        return $this;
    }


    public function setNewCampaignPlacement($placement){
        $table = $this->getTable("zolagocampaign/campaign_placement");

        $vendor_id = $placement['vendor_id'];
        $category_id = $placement['category_id'];
        $campaign_id = $placement['campaign_id'];
        $banner_id = $placement['banner_id'];
        $type = $placement['type'];
        $position = $placement['position'];
        $priority = $placement['priority'];

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
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
    public function setCampaignPlacements($categoryId, $vendorId, array $placements)
    {
        $table = $this->getTable("zolagocampaign/campaign_placement");
        $where = "category_id={$categoryId} AND vendor_id={$vendorId}";
        $this->_getWriteAdapter()->delete($table, $where);

        if (count($placements)) {
            $this->_getWriteAdapter()->insertMultiple($table, $placements);
        }
        return $this;
    }

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
     * @return array
     */
    public function getCategoryPlacements($categoryId, $vendorId, $bannerTypes = array(), $notExpired = FALSE)
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
        $select->where("campaign_placement.category_id=?", $categoryId);
        //$select->where("campaign.vendor_id=campaign_placement.vendor_id");
        $select->where("campaign_placement.vendor_id=?", $vendorId);
        if(!empty($bannerTypes)){
            $select->where("banner.type in(?)", $bannerTypes);
        }
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
     *
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

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param array $websites
     * @return Zolago_Campaign_Model_Resource_Campaign
     */
    protected function _setWebsites(Mage_Core_Model_Abstract $object, array $websites)
    {
        $table = $this->getTable("zolagocampaign/campaign_website");
        $where = $this->getReadConnection()
            ->quoteInto("campaign_id=?", $object->getId());
        $this->_getWriteAdapter()->delete($table, $where);

        $toInsert = array();
        foreach ($websites as $websiteId) {
            $toInsert[] = array("website_id" => $websiteId, "campaign_id" => $object->getId());
        }
        if (count($toInsert)) {
            $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
        }
        return $this;
    }
    /**
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
    public function getAllowedWebsites(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            return array();
        }
        $table = $this->getTable("zolagocampaign/campaign_website");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign_website" => $table), array("website_id"));
        $select->where("campaign_website.campaign_id=?", $object->getId());
        return $this->getReadConnection()->fetchCol($select);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
    public function getCampaignProducts(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            return array();
        }
        $collection = Mage::getResourceModel("catalog/product_collection")
            ->addAttributeToSelect('skuv');
        $collection->getSelect()
            ->join(
                array('campaign_product' => 'zolago_campaign_product'),
                'campaign_product.product_id = e.entity_id')
            ->where("campaign_product.campaign_id=?", $object->getId());
        $skuvS = array();
        foreach ($collection as $collectionItem) {
            $skuvS[] = $collectionItem->getSkuv();
        }

        return $skuvS;
    }


    /**
     * @return array
     */
    public function getProductCampaign()
    {
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table), array());
        $select->joinLeft(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                 'campaign_id' => 'campaign.campaign_id',
                 'campaign_name' => 'campaign.name'
            )
        );

        $select->where(
            "campaign.type IN (?)",
            array(Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION, Zolago_Campaign_Model_Campaign_Type::TYPE_SALE)
        )
        ->distinct(true);

        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getProductCampaignInfo()
    {
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table), array());
        $select->joinLeft(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                 'campaign_id'   => 'campaign.campaign_id',
                 'campaign_name' => 'campaign.name'
            )
        );
        $select->where(
            "campaign.type  IN (?)", array(Zolago_Campaign_Model_Campaign_Type::TYPE_INFO)
        )
        ->distinct(true);

        return $this->getReadConnection()->fetchAll($select);
    }


    public function getProductsInfoCampaigns($ids){
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table), array());
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'campaign_id'   => 'campaign.campaign_id',
                'product_id' => 'campaign_product.product_id'
            )
        );
        $select->where("campaign_product.product_id IN (?)", $ids);
        $select->where(
            "campaign.type  IN (?)", array(Zolago_Campaign_Model_Campaign_Type::TYPE_INFO)
        )
        ;

        return $this->getReadConnection()->fetchAll($select);
    }


    public function getExpiredCampaigns(){
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table), array("campaign.type as type",'campaign.campaign_id as campaign_id'));
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'product_id'   => 'campaign_product.product_id'
            )
        );
        $select->where('campaign.date_to', array('lteq' => date("Y-m-d H:i")));
        return $this->getReadConnection()->fetchAll($select);
    }

    public function getUpDateCampaigns(array $type)
    {
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(
            array("campaign" => $table),
            array(
                 "campaign.type as type",
                  'campaign.campaign_id as campaign_id',
                  'campaign.price_source_id as price_source',
                  'campaign.percent as price_percent',
                  'campaign.price_srp as price_srp'
            )
        );
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                 'product_id' => 'campaign_product.product_id'
            )
        );
        $startTime = date("Y-m-d H:i", strtotime('-10 minutes', time()));
        $endYTime = date("Y-m-d H:i", strtotime('+10 minutes', time()));

        $select->where("campaign.date_from BETWEEN '{$startTime}' AND '{$endYTime}'");
        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);

        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * Get campaigns with banners
     * @return array
     */
    public function getCampaigns()
    {
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendor = $vendor->getId();

        $result = array();

        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table),
            array(
                "campaign.campaign_id",
                "campaign.name",
                "campaign.date_from",
                "campaign.date_to",
                "campaign.vendor_id"
            )
        );
        $select->join(
            array('banner' => 'zolago_banner'),
            'banner.campaign_id = campaign.campaign_id',
            array("banner.type as banner_type")
        );
        if($vendor !== Mage::helper('udropship')->getLocalVendorId()){
            $select->where('campaign.vendor_id=?', $vendor);
        }

        $select->order("campaign.date_from DESC");

        try {
            $result = $this->getReadConnection()->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException($e);

        }

        return $result;
    }


    public function getCategoriesWithPath($path)
    {
        $table = "catalog_category_entity_varchar";
        $select = $this->getReadConnection()->select();
        $select->from(array("catalog_category" => $table), array("catalog_category.value_id"));
        $select->join(
            array('attribute' => 'eav_attribute'),
            'attribute.attribute_id = catalog_category.attribute_id',
            array()
        );
        $select->where('attribute.attribute_code=?', 'url_path');
        $entityTypeID = Mage::getModel('catalog/category')->getResource()->getTypeId();
        $select->where('catalog_category.entity_type_id=?', $entityTypeID);
        $select->where('catalog_category.value=?', $path);

        return $this->getReadConnection()->fetchAll($select);
    }

}


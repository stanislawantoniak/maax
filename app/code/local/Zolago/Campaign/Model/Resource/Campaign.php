<?php

class Zolago_Campaign_Model_Resource_Campaign extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocampaign/campaign', "campaign_id");
    }


    /**
     * Set times
     * @param Mage_Core_Model_Abstract $object
     * @return type
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        // Times
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);

        if ($object->getVendorId() === "") {
            $object->setVendorId(null);
        }

        return parent::_prepareDataForSave($object);
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
     * Assign products to campaign
     * @param $campaignId
     * @param array $productIds
     * @return $this
     * @throws Exception
     */
    public function saveProducts($campaignId, array $productIds)
    {
        $model = Mage::getModel('zolagocampaign/campaign');
        $campaign = $model->load($campaignId);

        $campaignProducts = $this->getCampaignProducts($campaign);

        $productIdsOfCampaign = array_keys($campaignProducts);
        $productsToDelete = array_diff($productIdsOfCampaign, $productIds);

        if (!empty($productsToDelete)) {
            foreach ($productsToDelete as $productsToDeleteId) {
                $this->removeProduct($campaignId, $productsToDeleteId);
            }
        }

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

            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $localeTimeF = date("Y-m-d H:i", $localeTime);
            $model = Mage::getModel("zolagocampaign/campaign");
            $campaign = $model->load($campaignId);

            $campaign->setData('updated_at', $localeTimeF);
            $campaign->save();
        }
        return $this;
    }

    /**
     * remove product from campaign
     * @param $campaignId
     * @param $productId
     */
    public function removeProduct($campaignId, $productId)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $where = "campaign_id={$campaignId} AND product_id={$productId}";
        $this->_getWriteAdapter()->delete($table, $where);

        $model = Mage::getModel('zolagocampaign/campaign');
        $campaign = $model->load($campaignId);
        $campaignId = $campaign->getId();

        $websites = $this->getAllowedWebsites($campaign);

        if (!empty($websites)) {
            $recoverOptionsProducts = array();
            foreach ($websites as $websiteId) {
                $recoverOptionsProducts[$websiteId] = array($productId);
            }

            Mage::dispatchEvent(
                "campaign_product_remove_update_after",
                array(
                    'campaign_id' => $campaignId,
                    "revert_product_options" => $recoverOptionsProducts
                )
            );
        }

    }

    /**
     * Set field assigned_to_campaign to 0 to products of campaign
     * Used when vendor change campaign fields percent and price_source_id
     * @param $campaign
     */
    public function unsetCampaignProductsAssignedToCampaignFlag($campaign)
    {
        $campaignId = $campaign->getId();
        if (empty($campaignId)) {
            //new campaign (no products)
            return;
        }
        $products = $this->getCampaignProducts($campaign);
        if (empty($products)) {
            return;
        }

        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();
        foreach ($products as $productId => $skuV) {
            $write->update($table, array('assigned_to_campaign' => 0), array('`campaign_id` = ?' => $campaignId));
        }
    }

    /**
     * Set recalculate flag in all active campaigns for products
     * @param array $productIds
     * @return 
     */
     public function putProductsToRecalculate($campaignId,$productIds) {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();
        $write->update($table, array('assigned_to_campaign' => 0), array('`product_id` in (?)' => $productIds,'`campaign_id` = ?' => $campaignId));
     }
    /**
     * Set field assigned_to_campaign to 1 to product
     * Used when product attributes set by crone
     * @param $productId
     */
    public function setCampaignProductAssignedToCampaignFlag($campaignIds, $productId)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();
        $write->update($table, array('assigned_to_campaign' => 1), array('`product_id` = ?' => $productId,'`campaign_id` IN(?)' => $campaignIds));
    }

    /**
     * Set field assigned_to_campaign to 0 to product
     * Used when product attributes unset by crone
     * @param $productId
     */
    public function unsetCampaignProductAssignedToCampaignFlag($campaignId, $productIds)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();
        $write->update($table, array('assigned_to_campaign' => 1), array('`product_id` IN(?)' => $productIds,'`campaign_id`=?' => $campaignId));
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
        $select->where("campaign_website.website_id=?", Mage::app()->getWebsite()->getId());
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
            $skuvS[$collectionItem->getId()] = $collectionItem->getSkuv();
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
                 'campaign_name' => 'campaign.name',
                'campaign_vendor_id' => 'campaign.vendor_id'
            )
        );

        $select->where(
            "campaign.type IN (?)",
            array(Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION, Zolago_Campaign_Model_Campaign_Type::TYPE_SALE)
        );
        //$select->where( "campaign.vendor_id=(?)",5);
        $select->distinct(true);

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
                 'name_customer' => 'campaign.name_customer'
            )
        );
        $select->where(
            "campaign.type  IN (?)", array(Zolago_Campaign_Model_Campaign_Type::TYPE_INFO)
        );

        $select->distinct(true);

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


    public function getNotValidCampaigns()
    {
        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);

        $table = $this->getTable("zolagocampaign/campaign");
        $collection = Mage::getModel("zolagocampaign/campaign")
                ->getCollection();
        $collection->addFieldToFilter('status', Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $collection->addFieldToFilter('date_to', array('lt'=>$localeTimeF));

        foreach ($collection as $collectionItem) {
            $collectionItem->setData('status', Zolago_Campaign_Model_Campaign_Status::TYPE_ARCHIVE);
            $collectionItem->save();
            Mage::dispatchEvent(
                "campaign_save_after",
                array(
                    "campaign" => $collectionItem,
                )
            );                                                                                                                                
        }


        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table),
            array(
                "campaign.type as type",
                'campaign.campaign_id as campaign_id',
                'campaign.date_from',
                'campaign.date_to',
                'campaign.status',
                'campaign.vendor_id'
            )
        );
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'product_id' => 'campaign_product.product_id',
                'campaign_product.assigned_to_campaign'
            )
        );
        $select->join(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign_website.campaign_id=campaign.campaign_id',
            array(
                'website_id' => 'campaign_website.website_id'
            )
        );
        $activeCampaignStatus = Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE;
        $select->where("campaign_product.assigned_to_campaign=0");
        $select->where("campaign.status <> ?",$activeCampaignStatus);
        return $this->getReadConnection()->fetchAll($select);
    }
    protected function _getCampaignsAttributesId() {
        $table = $this->getTable("eav/attribute");
        $select = $this->getReadConnection()->select();
        $select->from(
            array('eav' => $table),
            array ('attribute_id',
                    'attribute_code'
                )
        );
        $select->where('attribute_code in (?)',array ('visibility'));
        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * @param array $type
     * @return array
     */
    public function getUpDateCampaignsInfo()
    {
        $ids = $this->_getCampaignsAttributesId();
        $codeToId = array();
        foreach ($ids as $id) {
            $codeToId[$id['attribute_code']] = $id['attribute_id'];
        }
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->distinct(true)->from(
            array("campaign" => $table),
            array(
                "campaign.type as type",
                'campaign.campaign_id as campaign_id',
                'campaign.price_source_id as price_source',
                'campaign.percent as price_percent',
                'campaign.price_srp as price_srp',
                'campaign.strikeout_type as strikeout_type',
                'campaign.date_from as date_from',
                'campaign.date_to as date_to',

                'campaign.updated_at'
            )
        );
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'product_id' => 'campaign_product.product_id',
                'campaign_product.assigned_to_campaign'
            )
        );
        $select->join(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign.campaign_id=campaign_website.campaign_id',
            array(
                'website_id' => 'campaign_website.website_id'
            )
        );

        $select->join(
            array('products_visibility' =>'catalog_product_entity_int'),
            'campaign_product.product_id=products_visibility.entity_id',
            array('products_visibility.store_id')
        );


        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);


        $select->where("campaign.date_from IS NULL OR campaign.date_from<=?", date("Y-m-d H:i", $localeTime));
        $select->where("campaign.date_to IS NULL OR campaign.date_to>'{$localeTimeF}'");

        $select->where("campaign.type IN(?)", Zolago_Campaign_Model_Campaign_Type::TYPE_INFO);

        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $select->where("campaign_product.assigned_to_campaign=?", 0);
        $select->where("products_visibility.attribute_id=?", $codeToId['visibility']);
        $select->where("products_visibility.value<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        //$select->where("campaign.date_from IS NOT NULL AND campaign.date_to IS NOT NULL ");
        $select->order('campaign.date_from DESC');
        $select->order('campaign.date_to ASC');

        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * @param $vendor
     * @return array
     */
    public function getUpDateCampaignsVendors()
    {
        $ids = $this->_getCampaignsAttributesId();
        $codeToId = array();
        foreach ($ids as $id) {
            $codeToId[$id['attribute_code']] = $id['attribute_id'];
        }
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->distinct(true)->from(
            array("campaign" => $table),
            array(
                'campaign.vendor_id'
            )
        );
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array()
        );
        $select->join(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign.campaign_id=campaign_website.campaign_id',
            array()
        );

        $select->join(
            array('products_visibility' =>'catalog_product_entity_int'),
            'campaign_product.product_id=products_visibility.entity_id',
            array()
        );

        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);


        $select->where("campaign.date_from IS NULL OR campaign.date_from<=?", date("Y-m-d H:i", $localeTime));
        $select->where("campaign.date_to IS NULL OR campaign.date_to>'{$localeTimeF}'");

        $select->where("campaign.type IN(?)", array(
            Zolago_Campaign_Model_Campaign_Type::TYPE_SALE,
            Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
        ));

        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);

        $select->where("products_visibility.attribute_id=?", $codeToId['visibility']);
        $select->where("products_visibility.value<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $select->where("campaign.date_from IS NOT NULL AND campaign.date_to IS NOT NULL ");
        $select->order('campaign.date_from DESC');
        $select->order('campaign.date_to ASC');


        return $this->getReadConnection()->fetchCol($select);
    }

    /**
     * @param $vendor
     * @return array
     */
    public function getUpDateCampaignsSalePromotion($vendor)
    {
        if(empty($vendor)){
            return;
        }
        $ids = $this->_getCampaignsAttributesId();
        $codeToId = array();
        foreach ($ids as $id) {
            $codeToId[$id['attribute_code']] = $id['attribute_id'];
        }
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->distinct(true)->from(
            array("campaign" => $table),
            array(
                "campaign.type as type",
                'campaign.campaign_id as campaign_id',
                'campaign.price_source_id as price_source',
                'campaign.percent as price_percent',
                'campaign.price_srp as price_srp',
                'campaign.strikeout_type as strikeout_type',
                'campaign.date_from as date_from',
                'campaign.date_to as date_to',
                'campaign.vendor_id',

                'campaign.updated_at'
            )
        );
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'product_id' => 'campaign_product.product_id',
                'product_assigned_to_campaign' => 'campaign_product.assigned_to_campaign'
            )
        );
        $select->join(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign.campaign_id=campaign_website.campaign_id',
            array(
                'website_id' => 'campaign_website.website_id'
            )
        );

        $select->join(
            array('products_visibility' =>'catalog_product_entity_int'),
            'campaign_product.product_id=products_visibility.entity_id',
            array('products_visibility.store_id')
        );
        /*
        $select->join(
            array('eav_attribute_visibility' =>'eav_attribute'),
            'eav_attribute_visibility.attribute_id=products_visibility.attribute_id',
            array()
        );*/

        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);


        $select->where("campaign.date_from IS NULL OR campaign.date_from<=?", date("Y-m-d H:i", $localeTime));
        $select->where("campaign.date_to IS NULL OR campaign.date_to>'{$localeTimeF}'");

        $select->where("campaign.type IN(?)", array(
            Zolago_Campaign_Model_Campaign_Type::TYPE_SALE,
            Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
        ));

        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $select->where("vendor_id=?", $vendor);
        $select->where("campaign_product.assigned_to_campaign=?", 0);
        $select->where("products_visibility.attribute_id=?", $codeToId['visibility']);
        $select->where("products_visibility.value<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $select->where("campaign.date_from IS NOT NULL AND campaign.date_to IS NOT NULL ");
        $select->order('campaign.date_from DESC');
        $select->order('campaign.date_to ASC');


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
        $select->order('campaign.date_to ASC');

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

    /**
     * @param $ids
     *
     * @return array $assoc
     */
    public function getVendorSkuAssoc($ids)
    {
        $assoc = array();

        if (empty($ids)) {
            return $assoc;
        }
        $readConnection = $this->_getReadAdapter();
        $tVarchar = $readConnection->getTableName('catalog_product_entity_varchar');
        $select = $readConnection->select();
        $select->from(
            array("product_varchar" => $tVarchar),
            array(
                "product_id" => "product_varchar.entity_id",
                "skuv" => "product_varchar.value",
                "store" => "product_varchar.store_id",
                "sku" => "product.sku"
            )
        );
        $select->join(
            array("attribute" => $this->getTable("eav/attribute")),
            "attribute.attribute_id = product_varchar.attribute_id",
            array()
        );
        $select->join(
            array("product" => $this->getTable("catalog/product")),
            "product.entity_id = product_varchar.entity_id",
            array("type" => "product.type_id")
        );
        $select->where("attribute.attribute_code=?", Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute'));
        $select->where("product_varchar.entity_id IN(?)", $ids);
        //TODO need to know what to do for configurable
        //$select->where("product.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

        $select->where("product.visibility<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);

        try {
            $assoc = $readConnection->fetchAssoc($select);
        } catch (Exception $e) {
            Mage::throwException("Error skuv");
        }

        return $assoc;
    }

    public function insertOptionsBasedOnCampaign($insert)
    {
        $insert = array_unique($insert);
        $lineQuery = implode(",", $insert);

        $catalogProductSuperAttributePricingTable = 'catalog_product_super_attribute_pricing';

        $insertQuery = sprintf(
            "
                    INSERT INTO  %s (product_super_attribute_id,value_index,pricing_value,website_id)
                    VALUES %s
                    ON DUPLICATE KEY UPDATE catalog_product_super_attribute_pricing.pricing_value=VALUES(catalog_product_super_attribute_pricing.pricing_value)
                    ", $catalogProductSuperAttributePricingTable, $lineQuery
        );


        try {
            $this->_getWriteAdapter()->query($insertQuery);

        } catch (Exception $e) {
            Mage::throwException("Error insertOptionsBasedOnCampaign");

            throw $e;
        }
    }
    public function setRebuildProductInValidCampaign($productsIds) {
        $readConnection = $this->_getReadAdapter();
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $readConnection->select();

        $select->from(array("campaign" => $table),"campaign.campaign_id");
        $select->join(
            array("product" => $this->getTable("zolagocampaign/campaign_product")),
            "product.campaign_id = campaign.campaign_id",
            "product.product_id"
            );
        $select->where("campaign.status = ?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $select->where("product.product_id in(?)", $productsIds);
        $_return = $readConnection->fetchAll($select);
        $campaigns = array();
        foreach ($_return as $val) {
            $campaigns[$val['campaign_id']][$val['product_id']] = $val['product_id'];
        }
        foreach ($campaigns as $campain => $products) {
            $this->putProductsToRecalculate($campain,$products);
        }
    }

    /**
     * Returns array of products ids if in valid campaign
     *
     * @param $productsIds
     * @param null $type
     * @return array
     */
    public function getIsProductsInValidCampaign($productsIds, $type = null) {

        $return = array();
        $readConnection = $this->_getReadAdapter();
        $table = $this->getTable("zolagocampaign/campaign_product");
        $select = $readConnection->select();

        $select->from(array("product" => $table),"product.product_id");
        $select->where("product.product_id IN(?)", $productsIds);

        $select->joinLeft(
            array("campaign" => $this->getTable("zolagocampaign/campaign")),
            "product.campaign_id = campaign.campaign_id"
            );

        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);

        $select->where("campaign.date_from IS NULL OR campaign.date_from<=?", date("Y-m-d H:i", $localeTime));
        $select->where("campaign.date_to IS NULL OR campaign.date_to>'{$localeTimeF}'");
        $select->where("campaign.status = 1");

        if(!empty($type)) {
            $select->where("campaign.type IN(?)", array($type));
        }

        $_return = $readConnection->fetchAll($select);

        foreach ($_return as $row) {
            $return[] = $row['product_id'];
        }

        return $return;
    }

    /**
     * return array with ids if product is in campaign with type
     * sale or promo
     * $type can be custom set
     * @see Zolago_Campaign_Model_Campaign_Type::TYPE_*
     *
     * Example return array
     * [32339<productId>] => Array
     *    (
     *    [0] => 16<campaignId>
     *    )
     *
     * @param $productsIds
     * @param $vendorId
     * @param bool $type
     * @return array
     */
    public function getIsProductsInSaleOrPromotion($productsIds, $vendorId, $type = false) {


        if(empty($vendorId)){
            return;
        }
        $ids = $this->_getCampaignsAttributesId();
        $codeToId = array();
        foreach ($ids as $id) {
            $codeToId[$id['attribute_code']] = $id['attribute_id'];
        }
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->distinct(true)->from(
            array("campaign" => $table),
            array(
                "campaign.type as type",
                'campaign.campaign_id as campaign_id',
                'campaign.price_source_id as price_source',
                'campaign.percent as price_percent',
                'campaign.price_srp as price_srp',
                'campaign.strikeout_type as strikeout_type',
                'campaign.date_from as date_from',
                'campaign.date_to as date_to',
                'campaign.vendor_id',

                'campaign.updated_at'
            )
        );
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'product_id' => 'campaign_product.product_id',
                'product_assigned_to_campaign' => 'campaign_product.assigned_to_campaign'
            )
        );
        $select->join(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign.campaign_id=campaign_website.campaign_id',
            array(
                'website_id' => 'campaign_website.website_id'
            )
        );

        $select->join(
            array('products_visibility' =>'catalog_product_entity_int'),
            'campaign_product.product_id=products_visibility.entity_id',
            array('products_visibility.store_id')
        );

        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);


        $select->where("campaign.date_from IS NULL OR campaign.date_from<=?", date("Y-m-d H:i", $localeTime));
        $select->where("campaign.date_to IS NULL OR campaign.date_to>'{$localeTimeF}'");

        if (!$type) {
            $select->where("campaign.type IN(?)", array(
                Zolago_Campaign_Model_Campaign_Type::TYPE_SALE,
                Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
            ));
        } else {
            $select->where("campaign.type IN(?)", array(
                $type
            ));
        }

        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $select->where("vendor_id=?", $vendorId);
        $select->where("campaign_product.product_id IN(?)", $productsIds);
        $select->where("campaign_product.assigned_to_campaign=1");
        $select->where("products_visibility.attribute_id=?", $codeToId['visibility']);
        $select->where("products_visibility.value<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $select->where("campaign.date_from IS NOT NULL AND campaign.date_to IS NOT NULL ");
        $select->order('campaign.date_from DESC');
        $select->order('campaign.date_to ASC');


        $_return = $this->getReadConnection()->fetchAll($select);

        $return = array();
        foreach ($_return as $row) {
            $return[$row['product_id']][] = $row['campaign_id'];
        }

        return $return;

    }
}


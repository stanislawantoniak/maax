<?php

class Zolago_Campaign_Model_Resource_Campaign extends Mage_Core_Model_Resource_Db_Abstract
{

    const PRODUCTS_COUNT_TO_SET_PRODUCTS_INFO = 2000;
    const PRODUCTS_COUNT_TO_SET_PRODUCTS_SALE = 1500;
    const PRODUCTS_COUNT_TO_UNSET_PRODUCTS = 2000;

    const CAMPAIGN_PRODUCTS_UNPROCESSED = 0;
    const CAMPAIGN_PRODUCTS_PROCESSED = 1;
    const CAMPAIGN_PRODUCTS_TO_DELETE = 2; //STATUS to recalculate attributes then delete from zolago_campaign_product table

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
     * Set for campaign corresponding website
     * Currently usage is one(campaign) to one(website)
     * DB structure make it one to many but for future(?) usages we don't change it
     * @param Mage_Core_Model_Abstract $object
     * @param $websites
     * @return Zolago_Campaign_Model_Resource_Campaign
     */
    protected function _setWebsites(Mage_Core_Model_Abstract $object, $websites)
    {
        $table = $this->getTable("zolagocampaign/campaign_website");
        $where = $this->getReadConnection()
            ->quoteInto("campaign_id=?", $object->getId());
        $this->_getWriteAdapter()->delete($table, $where);

        $toInsert = array();
        if (!is_array($websites)) {
            $websites = array($websites);
        }
        foreach ($websites as $websiteId) {
            $toInsert[] = array("website_id" => $websiteId, "campaign_id" => $object->getId());
        }
        if (count($toInsert)) {
            $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
        }
        return $this;
    }
    
    /**
     * add products to campaign from memory
     *
     */
    public function saveProductsFromMemory() {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName("zolagocampaign/campaign_product");
        $table_tmp = $resource->getTableName('zolagocampaign/campaign_product_tmp');
        $tableSalesRule = $resource->getTableName("salesrule/rule");
        $connection = $resource->getConnection('core_write');
        // clean products        
        $query = 'update '.$table.' as a inner join '.$tableSalesRule.' as b set a.assigned_to_campaign='.self::CAMPAIGN_PRODUCTS_TO_DELETE.' where a.campaign_id = b.campaign_id ';


        $connection->query($query);
        $query = 'replace into '.$table.' (product_id,campaign_id,assigned_to_campaign) select distinct product_id,campaign_id,0 from '.$table_tmp;
        $connection->query($query);
        $query = 'delete from '.$table_tmp;
        $connection->query($query);
                
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
        $toInsert = array();
        foreach ($productIds as $productId) {
            $toInsert[] = array(
                "campaign_id" => $campaignId,
                "product_id" => $productId,
                "assigned_to_campaign" => Zolago_Campaign_Model_Resource_Campaign::CAMPAIGN_PRODUCTS_UNPROCESSED
            );
        }

        if (!empty($toInsert)) {
            $table = $this->getTable("zolagocampaign/campaign_product");
            try {
                $this->_getWriteAdapter()
                    ->insertOnDuplicate(
                        $table,
                        $toInsert,
                        array('campaign_id', 'product_id', 'assigned_to_campaign')
                    );
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        /**
         * Send products to recalculate
         * to delete them later ( @see Zolago_Campaign_Model_Campaign::unsetCampaignAttributes )
         */
        $this->sendProductsToRecalculateThenDelete($campaign, $productsToDelete);

        return $this;
    }


    /**
     * Pure Delete product from table
     * @param $campaignId
     * @param $productId
     */
    public function deleteProductsFromTable($campaignId, $productId)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $where = "campaign_id={$campaignId} AND product_id={$productId}";
        try {
            $this->_getWriteAdapter()->delete($table, $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Pure Delete products from table
     * @param $campaignId
     * @param $productIds
     */
    public function deleteProductsFromTableMass($campaignId, $productIds)
    {

        try {

            $where = join(' AND ', array(
                $this->_getWriteAdapter()->quoteInto('campaign_id = ?', $campaignId),
                $this->_getWriteAdapter()->quoteInto('product_id IN(?)', $productIds)
            ));
            $this->_getWriteAdapter()->delete($this->getTable("zolagocampaign/campaign_product"), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Remove product from campaign:
     * a) Pure delete product from table
     * b)recover magento product instance attributes @see Zolago_Campaign_Model_Observer::productAttributeRevert
     * @param $campaignId
     * @param $productId
     */
    public function removeProduct($campaignId, $productId)
    {

        $model = Mage::getModel('zolagocampaign/campaign');
        $campaign = $model->load($campaignId);

        /**
         * Send products to recalculate
         * to delete them later ( @see Zolago_Campaign_Model_Campaign::unsetCampaignAttributes )
         */
        $this->sendProductsToRecalculateThenDelete($campaign, array($productId));

    }

    /**
     * Send products to recalculate
     * to delete them later ( @see Zolago_Campaign_Model_Campaign::unsetCampaignAttributes )
     * @param $campaign
     * @param $productIds
     */
    public function sendProductsToRecalculateThenDelete($campaign, $productIds)
    {
        $campaignId = $campaign->getId();
        if (empty($campaignId)) {
            //new campaign (no products)
            return;
        }

        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();

        try {
            $write->update($table,
                array('assigned_to_campaign' => self::CAMPAIGN_PRODUCTS_TO_DELETE),
                array(
                    'product_id IN (?)' => $productIds,
                    'campaign_id=?' => $campaignId
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * Send products to recalculate
     * @param $campaign
     */
    public function sendProductsToRecalculate($campaign)
    {
        $campaignId = $campaign->getId();
        if (empty($campaignId)) {
            //new campaign (no products)
            return;
        }

        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();

        try {
            $write->update($table, array('assigned_to_campaign' => self::CAMPAIGN_PRODUCTS_UNPROCESSED), array('`campaign_id` = ?' => $campaignId, 'assigned_to_campaign<>?' => self::CAMPAIGN_PRODUCTS_TO_DELETE));

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * Set recalculate flag in all active campaigns for products
     *
     * @param $campaignId
     * @param $productIds
     */
    public function putProductsToRecalculate($campaignId, $productIds)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();
        try {
            $write->update($table, array('assigned_to_campaign' => self::CAMPAIGN_PRODUCTS_UNPROCESSED), array('`product_id` in (?)' => $productIds, '`campaign_id` = ?' => $campaignId, "assigned_to_campaign<>?" => self::CAMPAIGN_PRODUCTS_TO_DELETE));

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Set field assigned_to_campaign to 1 to product
     * Used when product attributes set by crone
     * @param $campaignIds
     * @param $productId
     */
    public function setCampaignProductAssignedToCampaignFlag($campaignIds, $productId)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();

        try {
            $write->update($table, array('assigned_to_campaign' => self::CAMPAIGN_PRODUCTS_PROCESSED), array('`product_id` = ?' => $productId, '`campaign_id` IN(?)' => $campaignIds, "assigned_to_campaign<>?" => self::CAMPAIGN_PRODUCTS_TO_DELETE));

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Set field assigned_to_campaign to 0 to product
     *
     * @param $campaignId
     * @param $productIds
     */
    public function setProductsAsProcessedByCampaign($campaignId, $productIds)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $write = $this->_getWriteAdapter();
        try {
            $write->update($table, array('assigned_to_campaign' => self::CAMPAIGN_PRODUCTS_PROCESSED), array('`product_id` IN(?)' => $productIds, '`campaign_id`=?' => $campaignId, "assigned_to_campaign<>?" => self::CAMPAIGN_PRODUCTS_TO_DELETE));

        } catch (Exception $e) {
            Mage::logException($e);
        }
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
            ->where("campaign_product.campaign_id=?", $object->getId())
            ->where("campaign_product.assigned_to_campaign<>?", self::CAMPAIGN_PRODUCTS_TO_DELETE);

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


    /**
     * @param $productIds
     * @return array
     * @throws Exception
     */
    public function getNotValidCampaignInfoPerProduct($productIds)
    {
        $table = $this->getTable("zolagocampaign/campaign");

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
        $select->where("campaign_product.product_id IN(?)",$productIds);



        $orWhere = array();
        $orWhere[] = 'campaign.status <>' . Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE;
        $orWhere[] = 'campaign_product.assigned_to_campaign=' . self::CAMPAIGN_PRODUCTS_TO_DELETE;

        $select->where(join(" OR ", $orWhere));


        $select->order('campaign_product.product_id ASC');

        return $this->getReadConnection()->fetchAll($select);
    }


    /**
     * If campaign expired set campaign status to TYPE_ARCHIVE
     * @throws Exception
     */
    public function setExpiredCampaignsAsArchived()
    {
        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);

        $table = $this->getTable("zolagocampaign/campaign");
        $collection = Mage::getModel("zolagocampaign/campaign")
            ->getCollection();
        $collection->addFieldToFilter('status', Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $collection->addFieldToFilter('date_to', array('lt' => $localeTimeF));

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
    }

    /**
     * Get products that need to be recalculated from not valid campaigns
     * or products with assigned_to_campaign=2
     * @return array
     */
    public function getNotValidCampaignProducts()
    {
        $this->setExpiredCampaignsAsArchived();

        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $this->getTable("zolagocampaign/campaign")),
            array(
                'campaign.campaign_id as campaign_id',
                "campaign.type as type",
                'campaign.date_from',
                'campaign.date_to',
                'campaign.status',
                'campaign.vendor_id'
            )
        );
        $select->join(
            array('campaign_product' => $this->getTable("zolagocampaign/campaign_product")),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                'product_id' => 'campaign_product.product_id',
                'campaign_product.assigned_to_campaign'
            )
        );
        $select->join(
            array('campaign_website' => $this->getTable("zolagocampaign/campaign_website")),
            'campaign_website.campaign_id=campaign.campaign_id',
            array(
                'website_id' => 'campaign_website.website_id'
            )
        );
        $activeCampaignStatus = Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE;
        $select->where("campaign_product.assigned_to_campaign=?", self::CAMPAIGN_PRODUCTS_TO_DELETE);

        $orWhere = array();
        $orWhere[] = 'campaign.status <> ' . $activeCampaignStatus;
        $orWhere[] = 'campaign_product.assigned_to_campaign<>' . self::CAMPAIGN_PRODUCTS_PROCESSED;

        $select->orWhere(join(" AND ", $orWhere));

        $select->order('campaign_product.product_id ASC');
        $select->group("campaign_product.product_id");
        $select->limit(self::PRODUCTS_COUNT_TO_UNSET_PRODUCTS);

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
     * GET campaigns type info for products
     * @param $productIds product ids
     * @return array
     */
    public function getUpDateCampaignsInfoPerProduct($productIds)
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
        $select->where("campaign_product.product_id IN(?)", $productIds);
        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);

        $select->where("products_visibility.attribute_id=?", $codeToId['visibility']);
        $select->where("products_visibility.value<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);

        $select->order('campaign_product.product_id ASC');


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
        $select->where("campaign.date_from IS NOT NULL AND campaign.date_to IS NOT NULL ");
        $select->order('campaign_product.product_id ASC');
        $select->group("campaign_product.product_id");
        $select->limit(self::PRODUCTS_COUNT_TO_SET_PRODUCTS_INFO);

        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * //TODO remove function
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
     * @return array
     */
    public function getUpDateCampaignsSalePromotion()
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

        $select->where("campaign_product.assigned_to_campaign=?", 0);
        $select->where("products_visibility.attribute_id=?", $codeToId['visibility']);
        $select->where("products_visibility.value<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $select->where("campaign.date_from IS NOT NULL AND campaign.date_to IS NOT NULL ");
        $select->order('campaign_product.product_id ASC');
        $select->limit(self::PRODUCTS_COUNT_TO_SET_PRODUCTS_SALE);

        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * Get campaigns with banners
     *
     * @param $websiteId
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getCampaigns($websiteId)
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
            array('banner' => $this->getTable("zolagobanner/banner")),
            'banner.campaign_id = campaign.campaign_id',
            array("banner.type as banner_type")
        );
        $select->join(
            array('campaign_website' => $this->getTable("zolagocampaign/campaign_website")),
            'campaign_website.campaign_id = campaign.campaign_id',
            array("campaign_website.website_id as campaign_website")
        );
        if($vendor !== Mage::helper('udropship')->getLocalVendorId()){
            $select->where('campaign.vendor_id=?', $vendor);
        }

        $select->where('campaign_website.website_id=?', $websiteId);

        $select->order("campaign.date_from DESC");
        $select->order('campaign.date_to ASC');

        try {
            $result = $this->getReadConnection()->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException($e);

        }

        return $result;
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

    /**
     * Save product ids corresponding to campaign
     *
     * @param Zolago_Campaign_Model_Campaign|int $campaign
     * @param array $productIds
     * @return $this
     */
    public function saveProductsToMemory($campaign, $productIds = array()) {

        $campaignId = $campaign;
        if ($campaign instanceof Zolago_Campaign_Model_Campaign) {
            $campaignId = $campaign->getId();
        }

        $toInsert = array();
        foreach ($productIds as $productId) {
            $toInsert[] = array("campaign_id" => $campaignId, "product_id" => $productId);
        }
        if (!empty($toInsert)) {

            $chunked = array_chunk($toInsert, 500);
            foreach ($chunked as $data) {
                $this->_getWriteAdapter()->insertMultiple(
                    $this->getTable("zolagocampaign/campaign_product_tmp"),
                    $data);
            }
        }
        return $this;
    }

    public function truncateProductsFromMemory() {
        $table = $this->getTable("zolagocampaign/campaign_product_tmp");
        $this->_getWriteAdapter()->truncateTable($table);
        return $this;
    }
    
    /**
     * get campaigns filtered by landing_page_category and campaign_id
     *
     * @param array $categories
     * @param int $vendorId
     * @param array $campaigns campaing ids
     * @return array ids of campaigns
     */
     public function getLandingPagesByCategories($categories,$vendorId,$campaigns) {
        if (empty($categories)) {
            return array();
        }
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->distinct(true)->from(
            array("campaign" => $table),
            array(
                'campaign.campaign_id as campaign_id',
                'campaign.landing_page_category as category_id'
            )
        );


         $select->where("campaign.landing_page_category IN(?)", $categories);
         $select->where("campaign.campaign_id IN(?)", $campaigns);

         if ($vendorId) {
             $select->where("campaign.context_vendor_id = ?", $vendorId);
             $select->where("campaign.landing_page_context = ?", Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR);
         } else {
             $select->where("campaign.landing_page_context = ?", Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY);
         }


         $select->where("campaign.is_landing_page = ?", Zolago_Campaign_Model_Campaign_Urltype::TYPE_LANDING_PAGE);
         $localtime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
         $select->where("campaign.date_from < ?", $localtime);
         $select->where("campaign.date_to > ?", $localtime);
         $select->where("campaign.status = ?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);

        $_return = $this->getReadConnection()->fetchAll($select);

        $return = array();
        foreach ($_return as $row) {
            $return[$row['category_id']][] = $row['campaign_id'];
        }

        return $return;
     
         
     }


    /**
     * Get websites used in campaigns
     * @return array
     */
    public function getCampaignWebsites()
    {
        $campaignWebsites = array();


        $websiteNames = array();
        foreach (Mage::app()->getWebsites() as $websiteId => $website) {
            $websiteNames[$website->getId()] = $website->getName();
        }

        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
        $collection = Mage::getResourceModel("zolagocampaign/campaign_collection");
        $collection->getSelect()
            ->join(
                array('campaign_website' => Mage::getSingleton('core/resource')->getTableName(
                    "zolagocampaign/campaign_website"
                )),
                'campaign_website.campaign_id = main_table.campaign_id',
                array("website_id" => "campaign_website.website_id")
            );
        $collection->addVendorFilter($vendor);
        foreach ($collection as $collectionItem) {
            $campaignWebsites[$collectionItem->getWebsiteId()] = $websiteNames[$collectionItem->getWebsiteId()];
        }


        return $campaignWebsites;
    }
}


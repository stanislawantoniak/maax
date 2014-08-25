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


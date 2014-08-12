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
        Mage::log($object->getData());
        // Website Assignment
        if ($object->hasData("website_ids")) {
            $this->_setWebsites($object, $object->getData("website_ids"));
        }

        // Products Assignment
        if ($object->hasData("campaign_products")) {
            //Prepare data
            $productsStr = $object->getData("campaign_products");
            $skuS = array();
            if (is_string($productsStr)) {
                $skuS = array_map('trim', explode(",", $productsStr));
            }
            $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('skuv', array('in' => $skuS))
                ->getAllIds();
            $productIds = array();
            if (!empty($collection)) {
                foreach ($collection as $productId) {
                    $productIds[] = $productId;
                }
            }
            $websites = $object->getWebsiteIds();
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID);
            foreach (Mage::app()->getWebsites() as $website) {
                if (in_array($website->getId(), $websites)) {
                    foreach ($website->getGroups() as $group) {
                        $stores = $group->getStores();
                        foreach ($stores as $store) {
                            //$store is a store object
                            $storeIds[] = $store->getId();
                        }
                    }
                }
            }
            //-----Prepare data


            $this->_setProducts($object, $productIds);


//            $this->_setCampaignAttributes($object, $productIds, $storeIds);
//
//            //Assignment Special Price From Date and Special Price To Date
//            if ($object->hasData("date_from") || $object->hasData("date_to")) {
//                $this->_setSpecialPriceDate($productIds, $object->getData("date_from"), $object->getData("date_to"),$storeIds);
//            }
//
//            //Assignment Special Price
//            if ($object->hasData("price_source_id") && $object->hasData("percent")) {
//                $this->_setSpecialPrice($productIds, $object->getData("price_source_id"), $object->getData("percent"),$storeIds);
//            }
//            //Assignment Suggested Retail Price
//            if ($object->hasData("price_srp")) {
//                $this->_setSRPPrice($productIds, $object->getData("price_srp"),$storeIds);
//            }
//            Mage::getSingleton('catalog/product_action')
//                ->reindexAfterMassAttributeChange();
        }
        return parent::_afterSave($object);
    }

    public function _setSRPPrice($productIds, $srpPrice, $storeIds)
    {
        $actionModel = Mage::getSingleton('catalog/product_action');
        foreach ($storeIds as $storeId) {
            $attributesData = array('msrp' => $srpPrice);
            $actionModel
                ->updateAttributesNoIndex($productIds, $attributesData, $storeId);

        }
    }


    public function _setSpecialPrice($productIds, $priceSourceId, $percent,$storeIds)
    {
        $actionModel = Mage::getSingleton('catalog/product_action');

        $queueModel = Mage::getResourceModel('zolagocatalog/queue_pricetype');
        $skuvs = $queueModel->getVendorSkuAssoc($productIds);

        //Ping converter to get special price
        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable: check credentials");
            return;
        }
        $attr = Mage::getResourceModel('catalog/product')
            ->getAttribute(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);
        if ($attr->usesSource()) {
            $priceType = $attr->getSource()->getOptionText($priceSourceId);
        }
        foreach ($skuvs as $productId => $productData) {
            $vendorSku = $productData['skuv'];
            $sku = $productData['sku'];

            $res = explode('-', $sku);
            $vendorExternalId = (!empty($res) && isset($res[0])) ? (int)$res[0] : false;
            if (!$vendorExternalId) {
                return;
            }
            foreach ($storeIds as $storeId) {
                $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);
                Mage::log($newPrice);
                if (!empty($newPrice)) {
                    $newPriceWithPercent = $newPrice + $newPrice * ((int)$percent / 100);

                    $attributesData = array('special_price' => $newPriceWithPercent);
                    $actionModel
                        ->updateAttributesNoIndex(array($productId), $attributesData, $storeId);

                }
            }

        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param $skuS
     * @param $dateFrom
     * @param $dateTo
     */
    public function _setSpecialPriceDate($productIds, $dateFrom, $dateTo, $storeIds)
    {
        $actionModel = Mage::getSingleton('catalog/product_action');
        if (!empty($dateFrom)) {
            $attributesData = array('special_from_date' => date('Y-m-d', strtotime($dateFrom)));
            foreach ($storeIds as $storeId) {
                $actionModel
                    ->updateAttributesNoIndex($productIds, $attributesData, $storeId);
            }
        }
        if (!empty($dateTo)) {
            $attributesData = array('special_to_date' => date('Y-m-d', strtotime($dateTo)));
            foreach ($storeIds as $storeId) {
                $actionModel
                    ->updateAttributesNoIndex($productIds, $attributesData, $storeId);
            }
        }
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
     * @param Mage_Core_Model_Abstract $object
     * @param array $skuS
     * @return Zolago_Campaign_Model_Resource_Campaign
     */
    public function _setCampaignAttributes(Mage_Core_Model_Abstract $object, array $productIds, $storeIds)
    {
        $actionModel = Mage::getSingleton('catalog/product_action');

        $campaignInfoIds = $this->getProductsInfoCampaigns($productIds);

        $dataToUpdate = array();
        if (!empty($campaignInfoIds)) {
            foreach ($campaignInfoIds as $resItem) {
                $dataToUpdate[$resItem['product_id']][] = $resItem['campaign_id'];
            }
        }

        switch($object->getType()){
            case Zolago_Campaign_Model_Campaign_Type::TYPE_INFO:
                foreach($dataToUpdate as $productId => $campaignIds){
                    $campaignIds = array_merge($campaignIds , array($object->getId()));
                    $attributesData = array('campaign_info_id' => implode("," , $campaignIds));
                    foreach ($storeIds as $storeId) {
                        $actionModel
                            ->updateAttributesNoIndex(array($productId), $attributesData, $storeId);
                    }
                }

                break;
            case Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION:
                $attributesData = array('campaign_regular_id' => $object->getId());
                foreach ($storeIds as $storeId) {
                    $actionModel
                        ->updateAttributesNoIndex($productIds, $attributesData, $storeId);
                }
                break;
            case Zolago_Campaign_Model_Campaign_Type::TYPE_SALE:
                $attributesData = array('campaign_regular_id' => $object->getId());
                foreach ($storeIds as $storeId) {
                    $actionModel
                        ->updateAttributesNoIndex($productIds, $attributesData, $storeId);
                }
                break;
        }

        return $this;
    }
    /**
     * @param Mage_Core_Model_Abstract $object
     * @param array $skuS
     * @return Zolago_Campaign_Model_Resource_Campaign
     */
    protected function _setProducts(Mage_Core_Model_Abstract $object, array $productIds)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $where = $this->getReadConnection()
            ->quoteInto("campaign_id=?", $object->getId());
        $this->_getWriteAdapter()->delete($table, $where);

        $toInsert = array();

        foreach ($productIds as $productId) {
            $toInsert[] = array("campaign_id" => $object->getId(), "product_id" => $productId);
        }
        if (count($toInsert)) {
            $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
        }
        return $this;
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
                'campaign.date_from as date_from',
                'campaign.date_to as date_to',
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
        $select->join(
            array('campaign_website' => 'zolago_campaign_website'),
            'campaign_website.campaign_id=campaign.campaign_id',
            array(
                'website_id' => 'campaign_website.website_id'
            )
        );
        $startTime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(strtotime('-30 minutes', time())));;
        $endYTime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(strtotime('+30 minutes', time())));

        $select->where("campaign.date_from BETWEEN '{$startTime}' AND '{$endYTime}'");
        $select->where("type in(?)", $type);
        $select->where("status=?", Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $select->order('campaign.date_from ASC');

        return $this->getReadConnection()->fetchAll($select);
    }

}


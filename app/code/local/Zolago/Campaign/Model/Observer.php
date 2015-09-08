<?php

class Zolago_Campaign_Model_Observer
{
    /**
     * After add/update a campaign
     *
     * @param Varien_Event_Observer $observer
     */
    public function campaignAfterUpdate($observer)
    {

        $campaign = $observer->getCampaign();
        /* @var $campaign Zolago_Campaign_Model_Campaign */
        $campaignId = $campaign->getId();


        if (empty($campaignId)) {
            //not implement to new campaigns
            return;
        }
        $localeTime = Mage::getModel('core/date')->timestamp(time());

        //set to campaign products assigned_to_campaign = 0
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');
        $resourceModel->unsetCampaignProductsAssignedToCampaignFlag($campaign);

        if ($campaign->getIsLandingPage() == Zolago_Campaign_Model_Campaign_Urltype::TYPE_LANDING_PAGE) {
            $campaignType = $campaign->getType();
            //generate landing page url
            $landingPageUrl = "";

            if ($campaignType == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE || $campaignType == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION) {
                $landingPageUrl = "fq[" . Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE . "][0]=" . $campaignId;
            }
            if ($campaignType == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO) {
                $landingPageUrl = "fq[" . Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE . "][0]=" . $campaignId;
            }
            if (!empty($landingPageUrl)) {
                $campaign->setData("campaign_url", $landingPageUrl);
                $campaign->save();
            }
        }


        if ((strtotime($campaign->getData('date_to')) <= $localeTime)
            && ($campaign->getStatus() == Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE)
        ) {
            $campaign->setStatus(Zolago_Campaign_Model_Campaign_Status::TYPE_ARCHIVE);
            $campaign->save();
        }

    }

    static function setProductAttributes()
    {

        $productsIdsPullToSolr = array();

        /* @var $modelCampaign Zolago_Campaign_Model_Campaign */
        $modelCampaign = Mage::getModel('zolagocampaign/campaign');

        /* @var $model Zolago_Campaign_Model_Resource_Campaign */
        $model = Mage::getResourceModel('zolagocampaign/campaign');

        //1. Set campaign attributes
        //info campaign
        $campaignInfo = $model->getUpDateCampaignsInfo();

        $dataToUpdate = array();
        if (!empty($campaignInfo)) {
            foreach ($campaignInfo as $campaignInfoItem) {
                $dataToUpdate[$campaignInfoItem['website_id']][$campaignInfoItem['product_id']][] = $campaignInfoItem['campaign_id'];
            }
            unset($campaignInfoItem);
        }

        //set attributes
        if (!empty($dataToUpdate)) {
            $websitesToUpdateInfo = array_keys($dataToUpdate);
            /* @var $catalogHelper Zolago_Catalog_Helper_Data */
            $catalogHelper = Mage::helper('zolagocatalog');
            $storesToUpdateInfo = $catalogHelper->getStoresForWebsites($websitesToUpdateInfo);

            foreach ($dataToUpdate as $websiteId => $dataToUpdateInfo) {
                $storesI = isset($storesToUpdateInfo[$websiteId]) ? $storesToUpdateInfo[$websiteId] : false;
                if ($storesI) {
                    $productIdsInfoUpdated = $modelCampaign->setInfoCampaignsToProduct($dataToUpdateInfo, $storesI);
                    $productsIdsPullToSolr = array_merge($productsIdsPullToSolr, $productIdsInfoUpdated);
                }
            }
            unset($dataToUpdate);
        }



        //sales/promo campaign
        $campaignSalesPromo = array();

        $vendors = $model->getUpDateCampaignsVendors();
        foreach($vendors as $vendor){
            $campaignSalesPromoV = $model->getUpDateCampaignsSalePromotion($vendor);
            $campaignSalesPromo = array_merge($campaignSalesPromo,$campaignSalesPromoV);
        }

        $dataToUpdate = array();
        if (!empty($campaignSalesPromo)) {
            foreach ($campaignSalesPromo as $campaignSalesPromoItem) {
                if (isset($dataToUpdate[$campaignSalesPromoItem['website_id']][$campaignSalesPromoItem['product_id']])) {
                    //get one last updated campaign
                    continue;
                }
                if ($campaignSalesPromoItem['campaign_id'] == $campaignSalesPromoItem['product_assigned_to_campaign']) {
                    // already assigned
                    continue;
                }
                $dataToUpdate[$campaignSalesPromoItem['website_id']][$campaignSalesPromoItem['product_id']] = $campaignSalesPromoItem;
            }
            unset($campaignSalesPromoItem);
        }


        $salesPromoProductsData = array();

        if (!empty($dataToUpdate)) {
            foreach ($dataToUpdate as $websiteIdOptions => $dataToUp) {

                foreach ($dataToUp as $productId => $data) {
                    //collect data to change configurable product options
                    $salesPromoProductsData[$websiteIdOptions][$productId] = array(
                        'price_source' => $data['price_source'],
                        'price_percent' => $data['price_percent'],
                        'website_id' => $data['website_id'],


                        'date_from' => $data['date_from'],
                        'date_to' => $data['date_to'],
                        'campaign_type' => $data['type'],
                        'campaign_strikeout_price_type' => $data['strikeout_type'],
                        'campaign_id' => $data['campaign_id']
                    );
                }
            }
        }

        //2. Set options
        /* @var $modelCampaign Zolago_Campaign_Model_Campaign */
        $modelCampaign = Mage::getModel('zolagocampaign/campaign');
        foreach ($salesPromoProductsData as $websiteId => $salesPromoProductsDataH) {
            $productIdsSPUpdated = $modelCampaign->setProductOptionsByCampaign($salesPromoProductsDataH, $websiteId);
            if(!empty($productIdsSPUpdated)){
                $productsIdsPullToSolr = array_merge($productsIdsPullToSolr, $productIdsSPUpdated);
            }
        }
        if(empty($productsIdsPullToSolr)){
            return;
        }
//
//        //3. reindex

         //Better performance
        $indexer = Mage::getResourceModel('catalog/product_indexer_eav_source');
        /* @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source */
        $indexer->reindexEntities($productsIdsPullToSolr);

        $numberQ = 20;
        if (count($productsIdsPullToSolr) > $numberQ) {
            $productsToReindexC = array_chunk($productsIdsPullToSolr, $numberQ);
            foreach ($productsToReindexC as $productsToReindexCItem) {
                Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindexCItem);

            }
            unset($productsToReindexCItem);
        } else {
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsIdsPullToSolr);

        }
//
////        //4. push to solr
        Mage::dispatchEvent(
            "catalog_converter_price_update_after",
            array(
                "product_ids" => $productsIdsPullToSolr
            )
        );
    }

    static public function unsetCampaignAttributes()
    {
        /* @var $campaignModel Zolago_Campaign_Model_Campaign */
        $campaignModel = Mage::getModel("zolagocampaign/campaign");
        $campaignModel->unsetCampaignAttributes();
    }


    /**
     * revert product attributes after delete product from campaign
     * @param $observer
     */
    static function productAttributeRevert($observer)
    {
//        $revertProductOptions = array(
//            'website_id' => array(
//                    'product_id1',
//                    'product_id1'
//                )
//        );
        $campaignId = $observer->getCampaignId();
        $revertProductOptions = $observer->getRevertProductOptions();


        /* @var $model Zolago_Campaign_Model_Campaign */
        $model = Mage::getModel('zolagocampaign/campaign');
        $model->unsetProductAttributesOnProductRemoveFromCampaign($campaignId,$revertProductOptions);
    }

    /**
     * Attach products to campaign
     *
     * Podczepia produkty do kampanii na podstawie reguly cenowej koszyka (z zaznaczoną kampania).
     * Jezeli produkt spelnia warunki reguly to jest podczepiany do zadanej kampanii
     * Warunki dotyczace koszyka (qty, total itp) sa pomijane w procesie validacji
     * Atrybuty (cechy) produktowe sa brane pod uwage tylko te, ktore maja zaznaczone
     * is_used_for_promo_rules oraz
     * są widoczne oraz
     * ich fronted type to:
     * 'text', 'multiselect', 'textarea', 'date', 'datetime', 'select', 'boolean', 'price' oraz
     * ich zakres jest globalny lub per website (per store sa pomijane)
     *
     * NOTE: Pomimo, ze sprawdzanie odbywa sie per website (ustalony z kampanii)
     * to warunek kategorii jest ujednolicony do wszystkich zakresow
     *
     *
     * @param Aoe_Scheduler_Model_Schedule $object
     */
    public static function attachProductsToCampaignBySalesRule($object) {
        try {
            $startTime = self::getMicrotime();

            /* @var Zolago_Campaign_Helper_SalesRule $helper */
            $helper = Mage::helper("zolagocampaign/salesRule");

            /* Collecting sales rules START */
            $rulesColl = $helper->getSalesRuleCollection();
            $rulesColl->load();
            /* Collecting sales rules END */

            // Cleaning conditions because
            // Rules can have some Cart (quote) conditions
            /** @var Mage_SalesRule_Model_Rule $rule */
            foreach ($rulesColl as $rule) {
                $con = unserialize($rule->getConditionsSerialized()); // Only variables should be passed by reference
                $rule->setConditionsSerialized(serialize($helper->cleanConditions($con)));
            }

            /* Collecting products START */
//            $time = self::getMicrotime();
            $productsDataPerWebsite = $helper->getProductsDataForWebsites();
//            Mage::log("Loading products data: " . self::_formatTime(self::getMicrotime() - $time), null, 'mylog.log');
            /* Collecting products END */

            // Main processing loop
            /** @var Zolago_Campaign_Model_Resource_Campaign $campaignResource */
            $campaignResource = Mage::getResourceModel("zolagocampaign/campaign");
            $campaignResource->truncateProductsFromMemory(); // Cleaning temporary table

            /** @var Mage_SalesRule_Model_Rule $rule */
            foreach ($rulesColl as $rule) {
                $time = self::getMicrotime();
                Mage::log("Start processing rule ".$rule->getId(), null, 'mylog.log');

                $productIds = array();
                $campaignId = $rule->getCampaignId();

                /** @var Zolago_Campaign_Model_Campaign $campaign */
                $campaign = Mage::getModel("zolagocampaign/campaign")->load($campaignId);
                $websiteId = $campaign->getAllowedWebsites()[0];

                $websiteProducts = $productsDataPerWebsite[$websiteId];

                /** @var Zolago_Catalog_Model_Product $productObject */
                $productObject = Mage::getModel("zolagocatalog/product");
                $object = new Varien_Object();

                // $product is an array not object
                foreach ($websiteProducts as $product) {

                    // If configurable or visible simple
                    if ($product["type_id"] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
                        !$product["parent_id"]
                    ) {

                        $productObject->addData($product);
                        $productObject->setProduct($productObject);
                        $object->setAllItems(array($productObject));

                        $v = $rule->getConditions()->validate($object);
                        if ($v) {
//                            Mage::log((int)$product["entity_id"] . " in campaign " . $campaignId, null, 'mylog.log');
                            $productIds[] = (int)$product["entity_id"];
                        }
                    } else {
                        continue;
                    }
                }
                $campaignResource->saveProductsToMemory($rule->getCampaignId(), $productIds);
                unset($productIds);
                Mage::log("time: " . self::_formatTime(self::getMicrotime() - $time), null, 'mylog.log');
            }
            
            $campaignResource->saveProductsFromMemory(); // assign to campaign
            Mage::log("SUM TIME: " . self::_formatTime(self::getMicrotime() - $startTime), null, 'mylog.log');

        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    public static function _formatTime($t) {
        return round($t,4) . "s";
    }

    public static function getMicrotime(){
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
}
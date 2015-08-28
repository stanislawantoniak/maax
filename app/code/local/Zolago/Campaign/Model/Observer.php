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
        $localeTimeF = date("Y-m-d H:i", $localeTime);

        //set to campaign products assigned_to_campaign = 0
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');
        $resourceModel->unsetCampaignProductsAssignedToCampaignFlag($campaign);

        if($campaign->getData("is_landing_page") == 1){
            $campaignType = $campaign->getType();
            //generate landing page url
            $landingPageUrl = "";
            $nameCustomer = $campaign->getData("name_customer");
            ;
            if($campaignType == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE || $campaignType == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION){
                //fq[campaign_regular_id][0]=-50%25+Matterhorn++PODKOSZULKI+MĘSKIE
                $landingPageUrl = "fq[campaign_regular_id][0]=" . urlencode($nameCustomer);
            }
            if($campaignType == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO){
                //fq[campaign_info_id][0]=LP+50%25+rabatu+na+produkty+Esotiq+Publiczna+nazwa+kampanii
                $landingPageUrl = "fq[campaign_info_id][0]=" .  urlencode($nameCustomer);
            }
            if(!empty($landingPageUrl)){
                $campaign->setData("landing_page_url", $landingPageUrl);
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
     * @param Aoe_Scheduler_Model_Schedule $object
     */
    public static function attachProductsToCampaignBySalesRule($object) {

        /* @var Zolago_Campaign_Helper_SalesRule $helper */
        $helper = Mage::helper("zolagocampaign/salesRule");

        try {
            $startTime = self::getMicrotime();
            $startMemory = memory_get_usage();
            Mage::log("START memory usage" .round(((memory_get_usage()) / 1024) / 1024, 2) . ' mega bytes', null, 'mylog.log');

            /* Collecting products START */
            $time = self::getMicrotime();

            $allProductsColl = $helper->getProductsCollection();
            $allProductsColl->load();

            Mage::log("Product collection load: " . self::_formatTime(self::getMicrotime() - $time), null, 'mylog.log');
            Mage::log("Found: " . $allProductsColl->count(), null, 'mylog.log');
            Mage::log("", null, 'mylog.log');
            /* Collecting products END */


            /* Collecting sales rules START */
            $time = self::getMicrotime();

            $rulesColl = $helper->getSalesRuleCollection();
            $rulesColl->load();

            Mage::log("Rules collection load: " . self::_formatTime(self::getMicrotime() - $time), null, 'mylog.log');
            Mage::log("Found: " . $rulesColl->count(), null, 'mylog.log');
            Mage::log("", null, 'mylog.log');
            /* Collecting sales rules END */


            // Cleaning conditions because
            // Rules can have some Cart (quote) conditions
            /** @var Mage_SalesRule_Model_Rule $rule */
            foreach ($rulesColl as $rule) {
                $con = unserialize($rule->getConditionsSerialized()); // Only variables should be passed by reference
                $rule->setConditionsSerialized(serialize($helper->cleanConditions($con)));
            }

            // Main processing loop
            $time = self::getMicrotime();

            /** @var Zolago_Campaign_Model_Resource_Campaign $campaignResource */
            $campaignResource = Mage::getResourceModel("zolagocampaign/campaign");
            // Cleaning temporary table
            $campaignResource->truncateProductsFromMemory();

            /** @var Mage_SalesRule_Model_Rule $rule */
            foreach ($rulesColl as $rule) {
                $productIds = array();
                /** @var Zolago_Catalog_Model_Product $product */
                foreach ($allProductsColl as $product) {
                    // If configurable or visible simple
                    if ($product->isConfigurable() || !$product->getParentId()) {
                        $object = new Varien_Object();
                        $objectProduct = new Varien_Object();
                        $objectProduct->setProduct($product);
                        $objectProduct->addData($product->getData());
                        $object->setAllItems(array($objectProduct));

                        $v = $rule->getConditions()->validate($object);
                        if ($v) {
                            $productIds[] = (int)$product->getId();
                        }
                    } else {
                        continue;
                    }
                }

                Mage::log("For rule id: " . $rule->getId() . " found: " . count($productIds), null, 'mylog.log');
                Mage::log("", null, 'mylog.log');

                $campaignResource->saveProductsToMemory($rule->getCampaignId(), $productIds);

                Mage::log(round(((memory_get_usage() - $startMemory) / 1024) / 1024, 2) . ' mega bytes', null, 'mylog.log');

                unset($productIds);

                Mage::log(round(((memory_get_usage() - $startMemory) / 1024) / 1024, 2) . ' mega bytes', null, 'mylog.log');
            }

            Mage::log("END memory usage" . round(((memory_get_usage() - $startMemory) / 1024) / 1024, 2) . ' mega bytes', null, 'mylog.log');
            Mage::log("", null, 'mylog.log');
            Mage::log("Processing time: " . self::_formatTime(self::getMicrotime() - $time), null, 'mylog.log');
            Mage::log("", null, 'mylog.log');
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
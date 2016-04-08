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
        $resourceModel->sendProductsToRecalculate($campaign);

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

    /**
     * Set attributes to products according to valid campaigns
     * attributes to set
     *
     * for campaigns type=info: campaign_info_id
     *
     * for campaigns type=sale or promotion:
     * campaign_regular_id,
     * special_price,
     * campaign_strikeout_price_type,
     * special_from_date, special_to_date,
     * product_flag
     *
     *
     * @throws Exception
     */
    static function setProductAttributes()
    {

        $productsIdsPullToSolr = array();

        $productsIdsPullToBan = array();

        /* @var $modelCampaign Zolago_Campaign_Model_Campaign */
        $modelCampaign = Mage::getModel('zolagocampaign/campaign');

        /* @var $modelCampaignResource Zolago_Campaign_Model_Resource_Campaign */
        $modelCampaignResource = $modelCampaign->getResource();

        //1. Set campaign attributes
        //info campaign
        $campaignInfoData = $modelCampaignResource->getUpDateCampaignsInfo(); //Products need to be updated


        //Reformat by product_id
        $productIdsToUpdate = array();
        foreach ($campaignInfoData as $campaignInfoData) {
            $productIdsToUpdate[] = $campaignInfoData["product_id"];
            unset($campaignInfoData);
        }

        $campaignInfo = $modelCampaignResource->getUpDateCampaignsInfoPerProduct($productIdsToUpdate);


        //Reformat by product_id
        $reformattedData = array();
        foreach ($campaignInfo as $campaignInfoData) {
            $reformattedData[$campaignInfoData["website_id"]][$campaignInfoData["product_id"]][] = $campaignInfoData["campaign_id"];
            $websitesToUpdateInfo[$campaignInfoData["website_id"]] = $campaignInfoData["website_id"];
        }

        //set attributes
        if (!empty($reformattedData)) {

            /* @var $catalogHelper Zolago_Catalog_Helper_Data */
            $catalogHelper = Mage::helper('zolagocatalog');
            $storesToUpdateInfo = $catalogHelper->getStoresForWebsites($websitesToUpdateInfo);

            foreach ($reformattedData as $websiteId => $dataToUpdateInfo) {
                $storesI = isset($storesToUpdateInfo[$websiteId]) ? $storesToUpdateInfo[$websiteId] : false;
                if ($storesI) {
                    $productIdsInfoUpdated = $modelCampaign->setInfoCampaignsToProduct($dataToUpdateInfo, $storesI);
                    $productsIdsPullToSolr = array_merge($productsIdsPullToSolr, $productIdsInfoUpdated);
                }
            }
            unset($dataToUpdate, $websiteId);
        }

        //sales/promo campaign
        $campaignSalesPromo = $modelCampaignResource->getUpDateCampaignsSalePromotion();


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
        //Mage::log($salesPromoProductsData, null, "set_log_1.log");
        foreach ($salesPromoProductsData as $websiteId => $salesPromoProductsDataH) {
            $productIdsSPUpdated = $modelCampaign->setProductOptionsByCampaign($salesPromoProductsDataH, $websiteId);
            $productsIdsPullToBan[$websiteId] = $productIdsSPUpdated;
            if (!empty($productIdsSPUpdated)) {
                $productsIdsPullToSolr = array_merge($productsIdsPullToSolr, $productIdsSPUpdated);
            }
        }
        if (empty($productsIdsPullToSolr) || empty($productsIdsPullToBan))
            return;

        unset($websiteId);
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

        //4. push to solr
        //5. Varnish & Turpentine
        foreach ($productsIdsPullToBan as $websiteId => $productsIdsPullToBanIds) {
            $store = Mage::app()
                ->getWebsite($websiteId)
                ->getDefaultGroup()
                ->getDefaultStore();

            Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productsIdsPullToSolr, $store);
        }
        Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $productsIdsPullToSolr));

    }

    static public function unsetCampaignAttributes()
    {
        /* @var $campaignModel Zolago_Campaign_Model_Campaign */
        $campaignModel = Mage::getModel("zolagocampaign/campaign");
        $campaignModel->unsetCampaignAttributes();
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
    public static function attachProductsToCampaignBySalesRule($object)
    {
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
                $tmp = unserialize($rule->getConditionsSerialized()); // Only variables should be passed by reference
                $con = empty($tmp['conditions']) ? $tmp : $helper->cleanConditions($tmp);  // clean if conditions exists
                $rule->setConditionsSerialized(serialize($con));
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
                Mage::log("Start processing rule " . $rule->getId(), null, 'mylog.log');

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

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public static function _formatTime($t)
    {
        return round($t, 4) . "s";
    }

    public static function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Add flag 'have_specific_domain' into Admin Website Information edit form
     * Event: adminhtml_store_edit_form_prepare_form
     * @see Mage_Adminhtml_Block_System_Store_Edit_Form::_prepareForm()
     *
     * @param Varien_Event_Observer $observer
     */
    public function addFieldsToAdminStoreEdit($observer)
    {
        /** @var Zolago_Campaign_Helper_Data $hlp */
        $hlp = Mage::helper('zolagocampaign');
        /** @var Mage_Adminhtml_Block_System_Store_Edit_Form $block */
        $block = $observer->getData('block');
        $form = $block->getForm();
        $fieldset = $form->getElements()->searchById('website_fieldset');

        if (Mage::registry('store_type') == 'website') {
            $websiteModel = Mage::registry('store_data');
            if ($postData = Mage::registry('store_post_data')) {
                $websiteModel->setData($postData['website']);
            }

            $fieldset->addField('website_have_specific_domain', 'select', array(
                'name' => 'website[have_specific_domain]',
                'label' => $hlp->__('Have specific domain'),
                'note' => $hlp->__('YES if website have specified domain, otherwise NO'),
                'value' => $websiteModel->getHaveSpecificDomain(),
                'options' => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
            ));


            $vendors = Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash();
            asort($vendors);
            $vendors = array("" => $hlp->__(' -- Select Vendor Store Owner -- ')) + $vendors;

            $fieldset->addField('website_vendor_id', 'select', array(
                'name' => 'website[vendor_id]',
                'label' => $hlp->__('Own Store for vendor:'),
                'required' => true,
                'note' => $hlp->__('Website is Own Store for vendor'),
                'value' => $websiteModel->getVendorId(),
                'options' => $vendors,
            ));
            
            $fieldset->addField('vendor_sites_allowed','select', array (
                'name' => 'website[vendor_sites_allowed]',
                'label' => $hlp->__('Vendor sites allowed'),
                'value'  => $websiteModel->getVendorSitesAllowed(),
                'options' => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
            ));
            $fieldset->addField('is_preview_website','select', array (
                'name' => 'website[is_preview_website]',
                'label' => $hlp->__('Website is for preview'),
                'value'  => $websiteModel->getIsPreviewWebsite(),
                'options' => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
            ));
                
            $fieldset->addField('preview_website_login', 'text', array(
                'name' => 'website[website_login]',
                'label' => $hlp->__('Access login for preview:'),
                'required' => true,
                'value' => $websiteModel->getWebsiteLogin(),
            ));
            $fieldset->addField('preview_website_password', 'password', array(
                'name' => 'website[website_password]',
                'label' => $hlp->__('Access password for preview:'),
                'required' => true,
                'value' => $websiteModel->getWebsitePassword(),
            ));
            $block->setChild('form_after', $block->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap("website_have_specific_domain", "website_have_specific_domain")
                ->addFieldMap("website_vendor_id", "website_vendor_id")
                ->addFieldMap("is_preview_website", "is_preview_website")
                ->addFieldMap("preview_website_login", "preview_website_login")
                ->addFieldMap("preview_website_password", "preview_website_password")
                ->addFieldDependence("website_vendor_id", "website_have_specific_domain", "1")
                ->addFieldDependence( "preview_website_login","is_preview_website", "1")
                ->addFieldDependence( "preview_website_password","is_preview_website", "1")
            );
        }
    }
}
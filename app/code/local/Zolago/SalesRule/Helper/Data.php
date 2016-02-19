<?php
class Zolago_SalesRule_Helper_Data extends Mage_SalesRule_Helper_Data {

	public function getActiveSalesRules() {
		$rules = Mage::getResourceModel('salesrule/rule_collection')
			->addIsActiveFilter()
			->addFieldToFilter('coupon_type',2)
			->addFieldToFilter('use_auto_generation',1)
			->load();

		return $rules;
	}

	protected function getStoreId() {
		return Mage::app()->getStore()->getId();
	}

	/**
	 * @param Mage_SalesRule_Model_Rule $rule
	 * @return bool|Mage_SalesRule_Model_Coupon
	 */
	public function getUnusedCouponByRule($rule) {
		if(!$rule->getIsActive()) {
			return false;
		}

		/** @var Mage_SalesRule_Model_Coupon $coupon */
		$coupon = Mage::getResourceModel('salesrule/coupon_collection')
			->addFieldToFilter('newsletter_sent', 0)
			->addFieldToFilter('rule_id',$rule->getId())
			->setPageSize(1)
			->load()
			->getFirstItem();

		// $coupon type == 1 equals to auto-generated coupons
		if($coupon->getType() == 1) {
			return $coupon;
		} else {
			//Mage::log("All coupon codes for newsletter thank you emails are used or sales rule is misconfigured",null,"newsletter.log");
			return false;
		}
	}

    public static function analyzeCouponByCustomerRequest($code)
    {
        $error = '';
        if (empty($code)) {
            return;
        }
        $customerSession = Mage::getSingleton('customer/session');
        $groupId = 0;
        if($customerSession->isLoggedIn()){
            $groupId    = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        /* @var $couponCollection Mage_SalesRule_Model_Resource_Coupon_Collection */
        $couponCollection = Mage::getModel('salesrule/coupon')
            ->getCollection();
        $couponCollection
            ->getSelect()
            ->join(array('salesrule_website' => 'salesrule_website'),
                'salesrule_website.rule_id = main_table.rule_id',
                array('website_id'))
            ->join(array('salesrule_customer_group' => 'salesrule_customer_group'),
                'salesrule_customer_group.rule_id = main_table.rule_id',
                array('customer_group_id'))
            ->join(array('salesrule' => 'salesrule'),
                'salesrule.rule_id = main_table.rule_id',
                array('description', 'from_date', 'to_date', 'uses_per_customer', 'uses_per_coupon'));
        $couponCollection->addFieldToFilter('customer_group_id', $groupId);
        $couponCollection->addFieldToFilter('salesrule.is_active', 1);
        $couponCollection->addFieldToFilter('code', $code);
        $couponCollection->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
        $couponM = $couponCollection->getFirstItem();

        $salesRuleId = $couponM->getRuleId();
        $couponId = $couponM->getId();

        if (empty($couponId)) {
            return;
        }
        //check if coupon expired
        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);
        $expirationDate = $couponM->getData('expiration_date');
        if (!empty($expirationDate) && $expirationDate <= $localeTimeF) {
            return Mage::helper('zolagomodago')->__('The coupon is expired');
        }

        //coupon usage
        $couponUsage = Mage::getModel('salesrule/coupon/usage');
        $couponUsage->load($code, 'code');

        if ($couponUsage->getId()) {
            //check uses_per_coupon
            $usesPerCoupon = $couponM->getData('uses_per_coupon');
            $timesUsed = $couponUsage->getTimesUsed();

            if ($timesUsed >= (int)$usesPerCoupon) {
                return Mage::helper('zolagomodago')->__('The coupon code has already been used.');
            }


            //check usage_per_customer
            /**
             * SELECT sco.code, scu.customer_id, scu.times_used FROM salesrule_coupon sco
             * INNER JOIN salesrule_customer scu ON sco.rule_id = scu.rule_id
             * GROUP BY scu.customer_id;
             */

            if ($customerSession->isLoggedIn()) {
                $customer = $customerSession->getCustomer();
                $customerId = $customer->getId();

                if (!empty($customerId)) {
                    /* @var $couponUsageByCustomerM Mage_SalesRule_Model_Rule_Customer */
                    $couponUsageByCustomerM = Mage::getModel('salesrule/rule_customer');
                    $couponUsageByCustomerM = $couponUsageByCustomerM->loadByCustomerRule($customerId, $salesRuleId);
                    $couponUsageByCustomerMId = $couponUsageByCustomerM->getId();

                    if (!empty($couponUsageByCustomerMId)) {
                        $usagePerCustomer = $couponM->getData('usage_per_customer');
                        $timesUsedByCustomer = $couponUsageByCustomerM->getTimesUsed();

                        if ($timesUsedByCustomer >= (int)$usagePerCustomer) {
                            return Mage::helper('zolagomodago')->__('The coupon code has already been used.');
                        }
                    }
                }
            }
        }

        //check if coupon does not meet conditions
        if ($couponM->getId()) {
            return Mage::helper('zolagomodago')->__('The coupon does meet conditions') . ': ' . $couponM->getDescription();

        }

        return $error;
    }


    /**
     * @param $data
     * @return array
     */
    public function assignCouponsToSubscribers($data, $rulesForCustomer)
    {
        $subscribers = $data["subscribers"];
        $coupons = $data["coupons"];
        $dataToSend = $data["data_to_send"];

        if (empty($subscribers) || empty($coupons)) {
            return array("subscribers" => $subscribers, "coupons" => $coupons, "data_to_send" => $dataToSend);
        } else {
            $firstSubscriberId = key($subscribers);
            $firstSubscriberEmail = $subscribers[$firstSubscriberId];

            $customerCodes = array();

            foreach ($coupons as $ruleId => $couponIds) {
                $firstCouponId = key($couponIds);

                if(!empty($firstCouponId)){
                    if(!isset($rulesForCustomer[$ruleId]) || !in_array($firstSubscriberEmail,$rulesForCustomer[$ruleId])){
                        $dataToSend[$firstSubscriberEmail][$ruleId] = $firstCouponId;
                        $customerCodes[$ruleId] = $firstCouponId;

                        $dataToSend = array_merge($dataToSend, array($firstSubscriberEmail => $customerCodes));
                    }

                }

                unset($coupons[$ruleId][$firstCouponId]);
            }
            unset($subscribers[$firstSubscriberId]);

            $data = array(
                "subscribers" => $subscribers,
                "coupons" => $coupons,
                "data_to_send" => $dataToSend
            );

            return $this->assignCouponsToSubscribers($data, $rulesForCustomer);
        }
    }


    /**
     * returns path to catalog with promotions images
     *
     * @return string
     */
    public function getPromotionImagePath()
    {
        //$path = Mage::getBaseDir('media') . DS . Zolago_SalesRule_Model_Promotion_Image::PROMOTION_IMAGE_PATH;
        $path = Mage::getBaseDir('media') . DS . Zolago_Campaign_Model_Campaign::LP_COUPON_IMAGE_FOLDER;
        return $path;
    }

	/**
	 * returns path to catalog with resized promotions images
	 *
	 * @return string
	 */
	public function getPromotionResizedImagePath($width=294, $height=194) {
		$path = $this->getPromotionImagePath() . DS . 'resized' . DS . $width;
		return $path;
	}


    /**
     * returns url to catalog with promotions images
     *
     * @return string
     */
    public function getPromotionImageUrl()
    {
        $path = Mage::getBaseUrl('media') . Zolago_Campaign_Model_Campaign::LP_COUPON_IMAGE_FOLDER;
        return $path;
    }

    /**
     * returns url to catalog with campaign coupon images
     *
     * @return string
     */
    public function getCampaignCouponImageUrl()
    {
        $path = Mage::getBaseUrl('media') . Zolago_Campaign_Model_Campaign::LP_COUPON_IMAGE_FOLDER;
        return $path;
    }

	/**
	 * returns url to catalog with resized promotions images
	 *
	 * @return string
	 */
	public function getPromotionResizedImageUrl($width = 480) {
		$path = $this->getCampaignCouponImageUrl() . DS . 'resized' . DS . $width;
		return $path;
	}

	protected function _resizePromotionImage($fileName,$width=294, $height=194) {
		$basePath = $this->getPromotionImagePath() . DS . $fileName;
		$newPath = $this->getPromotionResizedImagePath($width, $height) . DS . $fileName;
		//if width empty then return original size image's URL
		if ($width != '') {
			//if image has already resized then just return URL
			if (file_exists($basePath) && is_file($basePath) && !file_exists($newPath)) {
				$imageObj = new Varien_Image($basePath);
				$imageObj->constrainOnly(true);
				$imageObj->keepAspectRatio(true);
				$imageObj->keepFrame(false);
				$imageObj->resize($width, $height);
				$imageObj->save($newPath);
			} 
        }
	}

	protected function _resizePromotionVendorLogo($fileName,$width=104, $height=31) {
		$basePath = Mage::getBaseDir('media') . DS . $fileName;
		$newPath = $this->getPromotionVendorLogoResizedPath($width) . DS . $fileName;
		//if width empty then return original size image's URL
		if ($width != '') {
			//if image has already resized then just return URL
			if (file_exists($basePath) && is_file($basePath) && !file_exists($newPath)) {
				$imageObj = new Varien_Image($basePath);
				$imageObj->constrainOnly(true);
				$imageObj->keepAspectRatio(true);
				$imageObj->keepFrame(true);
				$imageObj->resize($width, $height);
				$imageObj->save($newPath);
			}
		}
	}
	
	public function getPromotionVendorLogoResizedPath($width = 104) {
		return Mage::getBaseDir('media') . DS . 'vendor' . DS . 'resized' . DS . $width;
	}

	public function getResizedPromotionImage($fileName,$width = 294, $height = 154) {

        $folderURL = $this->getCampaignCouponImageUrl();
		$imageURL = $folderURL . $fileName;

		//if width empty then return original size image's URL
		if ($width != '') {
			//if image has already resized then just return URL
			$this->_resizePromotionImage($fileName,$width, $height);
			$resizedURL = $this->getPromotionResizedImageUrl($width, $height) . DS . $fileName;
		} else {
			$resizedURL = $imageURL;
		}
		return $resizedURL;
	}


	/**
	 * @param Mage_Catalog_Model_Product $model
	 * @return array|null
	 */
	public function getResizedPromotionImageInfo($fileName) {
		$path = $this->getPromotionResizedImagePath() . DS . $fileName;
		// Extract cached image URI
		if(file_exists($path)){
			if($info=@getimagesize($path)){
				return array("width"=>$info[0], "height"=>$info[1], "ratio"=>100 * round(($info[1]/$info[0]),2));
			}
		}
		return null;
	}

    protected function _prepareCollection($ids)
    {
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->addFieldToFilter('coupon_id', array('in' => $ids));
        $out = array();
        foreach ($collection as $item) {
	        /** @var Zolago_SalesRule_Model_Rule $rule */
            $rule = Mage::getModel('salesrule/rule')->load($item['rule_id']);
            $campaignId = $rule->getCampaignId();
            $couponImage = $vendorLogo = NULL;
            if ($campaignId) {
	            /** @var Zolago_Campaign_Model_Campaign $campaign */
                $campaign = Mage::getModel("zolagocampaign/campaign")->load($campaignId);
                if ($campaign) {
                    $couponImage = $campaign->getCouponImage();
	                $vendor = $campaign->getVendor();
	                $vendorLogo = $vendor->getLogo();
	                $vendorName = $vendor->getVendorName();
	                if(!$vendorLogo) {
		                $vendorLogo = Mage::getDesign()->getSkinUrl("images/logo_black.png");
	                }
                }
            }
            $rule->setPromoImage($couponImage);
	        $rule->setVendorLogo($vendorLogo);
	        $rule->setVendorName($vendorName);
            $item['ruleItem'] = $rule;
            $out[] = $item;

            unset($rule);
            unset($campaign);
            unset($couponImage);
        }
        return $out;
    }

    protected function _changeDesign($area,$pack,$theme) {

		Mage::getDesign()->
		    setArea($area)->
			setPackageName($pack)->
			setTheme($theme);

    }
    /**    
     * send mail to customer with coupons_id
     * 
     * @param int $customer_id
     * @param array $ids salesrule_coupons primary keys
     * @return bool
     */
     public function sendPromotionEmail($customer_id,$ids) {
         if (empty($ids)) {
             return false;
         }
         $customer = Mage::getModel('customer/customer')->load($customer_id);
         $store = $customer->getStore();
         $oldStore = Mage::app()->getStore();
         $oldPack = Mage::getDesign()->getPackageName();
 		 $oldTheme = Mage::getDesign()->getTheme('skin');
 		 $oldArea = Mage::getDesign()->getArea();
         Mage::app()->setCurrentStore($store->getId());
 		 $this->_changeDesign('frontend',$store->getConfig("design/package/name"), $store->getConfig("design/theme/skin"));
         $template = Mage::getStoreConfig('promo/promotions_mail_settings/mail_template');
         $content = Mage::app()->getLayout()->createBlock('zolagosalesrule/email_promotion','zolagosalesrule.email.promotion');
         $list = $this->_prepareCollection($ids);
         $content->setPromotions($list);
         $data = array (
             'use_attachments'=> true,
             'promotionList' => $content->toHtml(),
             'store_name' => $store->getName(),
             'year'  => Mage::getModel('core/date')->date('Y')
         );
         $addedFiles = $addedLogos = array();
         foreach ($list as $item) {

             $name = $item['ruleItem']->getPromoImage();
             if ($name && !in_array($name, $addedFiles)) {
                 $this->_resizePromotionImage($name,280);
                 $data['_ATTACHMENTS'][] = array (
                     'filename' => $this->getPromotionResizedImagePath(280).DS.$name,
                     'id' => $name,
                     'disposition' => 'inline',                 
                 );
                 $addedFiles[] = $name;
              }

	         $vendorLogo = $item['ruleItem']->getVendorLogo();
	         if($vendorLogo && !in_array($vendorLogo,$addedLogos)) {
		         $this->_resizePromotionVendorLogo($vendorLogo);
		         $data['_ATTACHMENTS'][] = array (
			         'filename' => $this->getPromotionVendorLogoResizedPath() . DS . $vendorLogo,
			         'id' => $vendorLogo,
			         'disposition' => 'inline',
		         );
		         $addedLogos[] = $vendorLogo;
	         }

          }
         $helper = Mage::helper('zolagocommon');
         $sender = Mage::getStoreConfig('promo/promotions_mail_settings/mail_identity');
         $helper->sendEmailTemplate(
             $customer->getEmail(),
             '',
             $template,
             $data,
             $store->getId(),
             $sender
         );
         Mage::app()->setCurrentStore($oldStore);
         $this->_changeDesign($oldArea,$oldPack,$oldTheme);
         return true;             
     }

    public function getSalesRulesForSubscribers()
    {

        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $query = $readConnection
            ->select()
            ->from(
                array('salesrule_rule' => $resource->getTableName("salesrule/rule")),
                array("rule_id", "name", "from_date", "to_date", "coupon_type", "campaign_id")
            )
            ->joinLeft(array('salesrule_coupon' => $resource->getTableName("salesrule/coupon")),
                'salesrule_rule.rule_id = salesrule_coupon.rule_id',
                array("coupon_id", "code")
            )
            ->join(
                array("campaign" => $resource->getTableName("zolagocampaign/campaign")),
                "salesrule_rule.campaign_id = campaign.campaign_id",
                array(
                    "coupon_image" => "campaign.coupon_image"
                )
            )
            ->where('salesrule_rule.is_active = ?', 1)
            ->where('customer_id IS NULL')
            ->where('salesrule_rule.to_date >= ?', date("Y-m-d", $currentTimestamp))
            ->where('salesrule_rule.promotion_type = ?', Zolago_SalesRule_Model_Promotion_Type::PROMOTION_SUBSCRIBERS)

            //campaign conditions
            ->where('campaign.coupon_image IS NOT NULL')
            ->where('campaign.coupon_image<>?','')
            ->where('is_landing_page=?', 1)
            ->where('campaign.status=?', Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE)
            //--campaign conditions

        ;

        return $readConnection->fetchAll($query);
    }

	public function getSalesRulesCouponsByCustomers(array $customerIds) {
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');

		$queryRules = $readConnection
			->select()
			->from(
				array('salesrule_coupon' => $resource->getTableName("salesrule/coupon")),
				array("rule_id", "customer_id")           )
			->where('salesrule_coupon.customer_id IN(?)', $customerIds)
		;

		return $readConnection->fetchAll($queryRules);
	}

	public function areCouponsForCustomerAvailable($customerId) {
		//are any rules for subscribers available?
		$rules = $this->getSalesRulesForSubscribers();
		if(empty($rules)) {
			return false;
		}
		$availableRulesIds = array();
		foreach($rules as $rule) {
			if(array_search($rule['rule_id'],$availableRulesIds) === false) {
				$availableRulesIds[] = $rule['rule_id'];
			}
		}
		$customerAssignedRules = $this->getSalesRulesCouponsByCustomers(array($customerId));
		//if customer has no assigned rules then return true as there are other available (checked above)
		if(empty($customerAssignedRules)) {
			return true;
		}
		//if it's not empty check if his assigned rules are all that he could get
		foreach($customerAssignedRules as $customerRule) {
			$searchResult = array_search($customerRule['rule_id'],$availableRulesIds);
			if($searchResult !== false) {
				unset($availableRulesIds[$searchResult]);
			}
		}
		return count($availableRulesIds) ? true : false;
	}
}
<?php
/**
 * block with list of promotions
 */


class Zolago_Modago_Block_Mypromotions extends Mage_Core_Block_Template
{
	protected $_customer;
	protected $_customer_id;
	protected $_subscribed;
	protected $_cms_block = false;
	protected $_logged;
	protected $_persistent;
	protected $_list = false;

    protected function _prepareLayout() {
	    /** @var Zolago_Customer_Model_Session $customerSession */
	    $customerSession = Mage::getSingleton('customer/session');
         $this->_logged = $customerSession->isLoggedIn();
         if ($this->_logged) {
	         $this->_customer = $customerSession->getCustomer();
             $this->_customer_id = $this->_customer->getId();

             $email = $this->_customer->getEmail();

	         /** @var Zolago_Newsletter_Model_Subscriber $subscriber */
	         $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

	         //treat unsubscribed as subscriber
	         $this->_subscribed = $subscriber->isSubscribed() ? true : $subscriber->getStatus() == Zolago_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED;


	         $hasCoupons = count($this->getPromotionList()) ? true : false;
	         if($this->_subscribed) {
		         if ($hasCoupons) {
			         $this->_cms_block = 'mypromotions_logged';
		         } else {
			         $this->_cms_block = 'mypromotions_logged_nocoupons';
		         }
	         } else {
		         if($hasCoupons) {
			         $this->_cms_block = 'mypromotions_logged_nonewsletter';
		         } else {
			         $this->_cms_block = 'mypromotions_logged_nocoupons_nonewsletter';
		         }
	         }
         } else {
             $helper = Mage::helper('persistent/session');         
             $this->_persistent = $helper->isPersistent();         
             $this->_customer_id = $helper->getSession()->getCustomerId();
             if ($this->_persistent && $this->_customer_id) {                 
                 $this->_cms_block = 'mypromotions_persistance';
             }
         }

        parent::_prepareLayout();
    }

    /**
     * Promo list for mypromotions page
     * @return array
     */
    public function getPromotionList()
    {
	    if($this->_list === false) {
		    /* Coupons collection */
		    $collection = Mage::getModel('salesrule/coupon')->getCollection();
		    $collection->addFieldToFilter('customer_id', $this->_customer_id);
		    $collection->getSelect()
			    ->join(array('salesrule' => 'salesrule'), 'salesrule.rule_id = main_table.rule_id', array('salesrule.use_auto_generation'))
			    ->where('expiration_date > ?', date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
			    ->where('(main_table.times_used < main_table.usage_limit) OR (main_table.usage_limit = 0)')
			    ->where('salesrule.use_auto_generation = 1');

		    //Mage::log($collection->getSelect()->__toString());

		    $out = array();
		    $rules = array();
		    foreach ($collection as $item) {
			    $rules[$item['rule_id']] = $item['rule_id'];
		    }
		    if (empty($rules)) {
			    $this->_list =  array();
			    return $this->_list;
		    }

		    //TODO clear unused fields from sales_rule
		    $rulesCollection = Mage::getModel('salesrule/rule')->getCollection();
		    $rulesCollection->addFieldToFilter('rule_id', array('in' => $rules));
		    $rulesCollection->addFieldToFilter('is_active', array('eq'=> 1));

		    $rulesCollection->getSelect()
			    ->join(
				    array("campaign" => "zolago_campaign"),
				    "main_table.campaign_id = campaign.campaign_id",
				    array(
					    "campaign_id" => "campaign.campaign_id",
					    //"is_landing_page" => "campaign.is_landing_page",
					    "coupon_image" => "campaign.coupon_image",
					    "landing_page_context" => "campaign.landing_page_context",
					    "context_vendor_id" => "campaign.context_vendor_id",
					    "coupon_pdf" => "campaign.coupon_conditions",
					    "campaign_url" => "campaign.campaign_url"
				    )
			    );

		    //jeśli kupon nie jest powiązany z kampanią (bo ktoś źle zdefiniował)
		    // lub brakuje obrazka - nie pokazujemy kuponu
		    $localtime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
		    $rulesCollection->addFieldToFilter('status', array("eq" => Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE));
		    $rulesCollection->addFieldToFilter('is_landing_page', array("eq" => 1));
		    $rulesCollection->addFieldToFilter('campaign.coupon_image', array('notnull' => true));
		    $rulesCollection->addFieldToFilter('campaign.coupon_image', array('neq' => ''));

		    $rulesCollection->addFieldToFilter('date_from', array('lteq' => $localtime));
		    $rulesCollection->addFieldToFilter('date_to', array('gteq' => $localtime));

		    $rules = array(); // clear

		    $vendorIds = array();


		    $campaignIds = array();
		    foreach ($rulesCollection as $rule) {
			    $rules[$rule['rule_id']] = $rule;
			    $vendorIds[] = $rule->getContextVendorId();
			    $campaignIds[] = $rule->getCampaignId();
		    }

		    $vendorIds = array_unique($vendorIds);
		    $campaignIds = array_unique($campaignIds);


		    $vendorLogos = array();
		    if (!empty($vendorIds)) {
			    foreach ($vendorIds as $vendorId) {
				    $campaignVendor = Mage::getModel("udropship/vendor")->load($vendorId);
				    if ($campaignVendor) {
					    $vendorLogos[$vendorId] = $campaignVendor->getLogo();
				    }
				    unset($campaignVendor);
			    }
		    }

		    //Get campaign landing page banners
		    if (!empty($campaignIds)) {
			    $landingPageBanners = Mage::helper("zolagocampaign/landingPage")->getLandingPageBanners($campaignIds);
		    }


		    $campaignDataForRule = array();
		    foreach ($rulesCollection as $ruleData) {

			    $campaignDataForRule[$ruleData->getRuleId()]["image"] = $ruleData->getCouponImage();
			    if (isset($landingPageBanners[$ruleData->getCampaignId()])) {
				    $ruleData->setLandingPageBanner($landingPageBanners[$ruleData->getCampaignId()]);
			    }
			    if ($ruleData->getLandingPageContext() == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR) {
				    $campaignDataForRule[$ruleData->getRuleId()]["logo_vendor"] = isset($vendorLogos[$ruleData->getContextVendorId()]) ? Mage::getBaseUrl("media") . $vendorLogos[$ruleData->getContextVendorId()] : "";
			    }
			    if ($ruleData->getCouponPdf())
				    $ruleData->setCouponPdf(Mage::getBaseUrl("media") . Zolago_Campaign_Model_Campaign::LP_COUPON_PDF_FOLDER . DS . $ruleData->getCouponPdf());


			    $ruleData->setExpirationDate(date('d.m.Y', Mage::getModel('core/date')->timestamp(
				    strtotime($ruleData->getToDate())
			    )));
			    $ruleData->setCampaignUrl(Mage::helper("zolagocampaign/landingPage")->getLandingPageUrl($ruleData->getCampaignId()), FALSE);
		    }

		    foreach ($collection as $item) {
			    if (isset($rules[$item['rule_id']])) {
				    $ruleItem = $rules[$item['rule_id']];
				    $ruleItem->setCouponImage($campaignDataForRule[$item['rule_id']]["image"]);

				    if (isset($campaignDataForRule[$item['rule_id']]["logo_vendor"])) {
					    $ruleItem->setLogoVendor($campaignDataForRule[$item['rule_id']]["logo_vendor"]);
				    }

				    $item['ruleItem'] = $ruleItem;
				    $out[] = $item;

				    unset($ruleItem);
			    }
		    }
		    $this->_list = $out;
	    }

        return $this->_list;
    }	
    /**
     * cms block 
     * @return string
     */
     public function getCmsBlock() {
        return $this->_cms_block ? $this->getLayout()->createBlock('cms/block')->setBlockId($this->_cms_block)->toHtml() : '';
     }
     
     public function isLogged() {
         return $this->_logged;
     }
     public function isPersistent() {
         return $this->_persistent;
     }
     public function isSubscribed() {
	     return $this->_subscribed;
     }
     public function getCustomerId() {
	     return $this->_customer_id;
     }

    public function getGalleryLogo(){
        return Mage::getDesign()->getSkinUrl("images/logo_black.png");
    }

    /**
     * returns url to catalog with promotions images
     *
     * @return string
     */
    public function getPromotionImageUrl()
    {
        $path = Mage::getBaseUrl('media') . Zolago_Campaign_Model_Campaign::LP_COUPON_IMAGE_FOLDER. DS ;
        return $path;
    }

    /**
     * Restrict Display "Copy to clipboard" link on Firefox
     * @return bool
     */
    public function showCopyToClipboard()
    {
        $showCopyLink = false;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
            if (strlen(strstr($agent, 'Firefox')) <= 0) {
                $showCopyLink = true;
            }
        }
        return $showCopyLink;
    }
} 
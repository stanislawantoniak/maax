<?php
/**
 * block with list of promotions
 */


class Zolago_Modago_Block_Mypromotions extends Mage_Core_Block_Template
{		
	protected $_customer_id;
	protected $_subscribed;
	protected $_cms_block;
	protected $_logged;
	protected $_persistent;

    protected function _prepareLayout() {
         $this->_logged = Mage::getSingleton('customer/session')->isLoggedIn();
         if ($this->_logged) {
             $this->_customer_id = Mage::getSingleton('customer/session')->getCustomerId();
             $customer = Mage::getModel('customer/customer')->load($this->_customer_id);
             $email = $customer->getEmail();
             $this->_subscribed = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->isSubscribed();
             $this->_cms_block = 'mypromotions_logged';
         } else {
             $helper = Mage::helper('persistent/session');         
             $this->_persistent = $helper->isPersistent();         
             $this->_customer_id = $helper->getSession()->getCustomerId();
             if ($this->_persistent && $this->_customer_id) {                 
                 $this->_cms_block = 'mypromotions_persistance';
             } else {
                 $this->_cms_block = 'mypromotions_not_logged';
             }
         }        
        parent::_prepareLayout();
    }
    public function getPromotionList() {
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->addFieldToFilter('customer_id',$this->_customer_id);
        $collection->getSelect()
	        ->join(array('salesrule'=>'salesrule'),'salesrule.rule_id = main_table.rule_id',array('salesrule.use_auto_generation'))
	        ->where('expiration_date > ?',date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
            ->where('(main_table.times_used < main_table.usage_limit) OR (main_table.usage_limit = 0)')
            ->where('salesrule.use_auto_generation = 1');
        $out = array();
        $rules = array();
        foreach ($collection as $item) {
            $rules[$item['rule_id']] = $item['rule_id'];            
        }
        if (empty($rules)) {
            return array();
        }
        $rulesCollection = Mage::getModel('salesrule/rule')->getCollection();
        $rulesCollection->addFieldToFilter('rule_id',array('in',$rules));
        $rules = array(); // clear

        $campaignRuleRelation = array();
        foreach ($rulesCollection as $rule) {
            $rules[$rule['rule_id']] = $rule;
            $campaignRuleRelation[$rule->getCampaignId()] = $rule['rule_id'];
        }
        $campaignIds = array_keys($campaignRuleRelation);

        $ruleCouponDataRelation = array();
        if(!empty($campaignIds)){
            $campaignCollection = Mage::getModel("zolagocampaign/campaign")->getCollection();
            $campaignCollection->addFieldToFilter('campaign_id',array('in',$campaignIds));

            $campaignCollection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns("campaign_id")
                ->columns("coupon_image")
                ->columns("coupon_conditions");

            foreach($campaignCollection as $campaignCollectionItem){
                $ruleCouponDataRelation[$campaignRuleRelation[$campaignCollectionItem->getCampaignId()]] = $campaignCollectionItem;
            }
        }

        foreach ($collection as $item) {
            if ($rules[$item['rule_id']]->getIsActive()) {
                $ruleItem = $rules[$item['rule_id']];

                $ruleItem->setCampaignData($ruleCouponDataRelation[$ruleItem->getId()]);

                $item['ruleItem'] = $ruleItem;
                $out[] = $item;

                unset($ruleItem);
            }
        }

        return $out;
    }	
    /**
     * cms block 
     * @return string
     */
     public function getCmsBlock() {
        return $this->getLayout()->createBlock('cms/block')->setBlockId($this->_cms_block)->toHtml();
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
} 
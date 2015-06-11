<?php
/**
 * blok with list of promotions
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
             if ($this->_subscribed) { 
                 $this->_cms_block = 'mypromotions_logged_subscribed';
             } else {
                 $this->_cms_block = 'mypromotions_logged_not_subscribed';
             }
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
        if (!$this->_customer_id) {
            return array();
        }
        if (!$this->_subscribed && $this->_logged) {
            return array();
        }
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->addFieldToFilter('customer_id',$this->_customer_id);
        $out = array();
        $rules = array();
        foreach ($collection as $item) {
            $rules[$item['rule_id']] = $item['rule_id'];            
        }
        $rulesCollection = Mage::getModel('salesrule/rule')->getCollection();
        $rulesCollection->addFieldToFilter('rule_id',array('in',$rules));
        $rules = array(); // clear 
        foreach ($rulesCollection as $rule) {
            $rules[$rule['rule_id']] = $rule;
        }
        foreach ($collection as $item) {
            $item['ruleItem'] = $rules[$item['rule_id']];
            $out[] = $item;
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
     
	
} 
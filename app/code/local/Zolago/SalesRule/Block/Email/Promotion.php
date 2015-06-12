<?php
/**
 * generate content for email with promotions
 */


class Zolago_SalesRule_Block_Email_Promotion extends Mage_Core_Block_Template
{		
	protected function _construct() {
	    $this->setTemplate('zolagosalesrule/email/promotions.phtml');
	    parent::_construct();
	}
	
	public function getPromotions() {
	    $ids = $this->getIds();
	    $collection = Mage::getModel('salesrule/coupon')->getCollection();
	    $collection->addFieldToFilter('coupon_id',array('in' => $ids));
	    $out = array();
	    foreach ($collection as $item) {
	        $item['ruleItem'] = Mage::getModel('salesrule/rule')->load($item['rule_id']);
	        $out[] = $item;
	    }
	    return $out;
	}
} 
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
	
} 
<?php

class Zolago_Po_Block_Vendor_Po_Grid_Column_Renderer_Alert
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$value = (int)$row->getData($this->getColumn()->getIndex());
		$return = array();
		
		if(($value & Zolago_Po_Model_Po_Alert::ALERT_SAME_EMAIL_PO) /*&& !$row->isFinished()*/){
			$filter = "customer_fullname=".  $row->getData("customer_email") . "&udropship_status=";;
			$link = $this->getUrl("udpo/vendor/index", array("filter"=>Mage::helper('core')->urlEncode($filter)));
			
			$return[] = $this->__(
					Zolago_Po_Model_Po_Alert::getAlertText(Zolago_Po_Model_Po_Alert::ALERT_SAME_EMAIL_PO), 
					'<a href="'.$link.'">' . $this->__("link") . '</a>'
			);
		}
		return implode("<br/>", $return);
	}
}

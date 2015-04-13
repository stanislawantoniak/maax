<?php

class Zolago_Po_Block_Vendor_Po_Grid_Column_Renderer_Alert
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$value = (int)$row->getData($this->getColumn()->getIndex());
		$return = array();

		/** @var Zolago_Po_Model_Po_Alert $alertModel */
		$alertModel = Mage::getModel("zolagopo/po_alert");

		foreach($alertModel->getAllOptions() as $alertBit=>$text) {
			if($value & $alertBit) {
				$link = false;
				switch($alertBit) {
					case $alertModel::ALERT_SAME_EMAIL_PO:
						$filter = "customer_fullname=".  $row->getData("customer_email") . "&udropship_status=";;
						$link = $this->getUrl("udpo/vendor/index", array("filter"=>Mage::helper('core')->urlEncode($filter)));
						break;
				}
				$return[] = $this->__(
					$alertModel->getAlertText($alertBit),
					($link ? '<a href="'.$link.'">' . $this->__("link") . '</a>' : null)
				);
			}
		}
		return implode("<br/><br/>", $return);
	}
}

<?php
class Zolago_Rma_Block_Vendor_Rma_Edit extends Mage_Core_Block_Template {
	
	
	/**
	 * @return Zolago_Rma_Model_Rma
	 */
	public function getModel() {
		if(!Mage::registry("current_rma")){
			 Mage::register("current_rma", Mage::getModel("zolagorma/rma"));
			 
		}
		return Mage::registry("current_rma");
	}
	
	
	
}


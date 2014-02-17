<?php
class Zolago_Pos_Block_Dropship_Pos_Edit extends Mage_Core_Block_Template {
	
	/**
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getModel() {
		if(!Mage::registry("current_pos")){
			 Mage::register("current_pos", Mage::getModel("zolagopos/pos"));
		}
		return Mage::registry("current_pos");
	}
	
	public function getIsNew() {
		return $this->getModel()->getId();
	}
}

?>

<?php
class Zolago_Catalog_Block_Vendor_Mass_Topfilter extends Mage_Core_Block_Topfilter {
    public function getGrid() {
		if($this->getParentBlock() && $this->getParentBlock()->getGrid()){
			return $this->getParentBlock()->getGrid();
		}
		return null;
	}
}
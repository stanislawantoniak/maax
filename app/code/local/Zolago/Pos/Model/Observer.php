<?php
class Zolago_Pos_Model_Observer {
	
	public function udpoOrderSaveBefore($observer) { // After
		$udpos = $observer->getUdpos();
		foreach($udpos as $udpo){
			/* @var $udpo Unirgy_DropshipPo_Model_Po */
			$this->_assignPosToPo($udpo);
		}
		
	}
	
	protected function _assignPosToPo($udpo) {
		/* @var $udpo Zolago_Po_Model_Po */
		if(!$udpo->getId() && !$udpo->getDefaultPosId()){
			$bestPos = $this->_getBestPosForPo($udpo);
			if($bestPos){
				/** @var Zolago_Pos_Model_Pos $bestPos */
				$udpo->setDefaultPosId($bestPos->getId());
				$udpo->setDefaultPosName($bestPos->getName());
			} else {
				/** @var Zolago_Dropship_Model_Vendor $vendor */
				$vendor = $udpo->getVendor();
				$posId = $vendor->getProblemPosId();
				if ($posId) {
					$pos = Mage::getModel('zolagopos/pos')->load($posId);
					$udpo->setDefaultPosId($posId);
					$udpo->setDefaultPosName($pos->getName());
				} else {
					// if no set problematic POS
					// we get first best for vendor connected to website from PO
					$posList = $vendor->getVendorPOSesPerWebsite($udpo->getStore()->getWebsiteId());
					$pos = $posList->getFirstItem();
					if ($pos->getId()) {
						$udpo->setDefaultPosId($pos->getId());
						$udpo->setDefaultPosName($pos->getName());
					} else {
						// if still no POS we get first available POS at all
						$posList = $vendor->getAllVendorPOSes();
						$pos = $posList->getFirstItem();
						if ($pos->getId()) {
							$udpo->setDefaultPosId($pos->getId());
							$udpo->setDefaultPosName($pos->getName());
						} else {
							// This have no sense
							// Vendor need to have at least one POS for selling
							$poId = $udpo->getId();
							$vId = $udpo->getVendor()->getId();
							Mage::logException(new Mage_Core_Exception("There was problem with assign POS to PO. po_id: {$poId} vendor_id: {$vId}"));
						}
					}
				}
			}
		}
	}

	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return bool|Varien_Object
	 */
	protected function _getBestPosForPo($po) {
		/** @var Zolago_Dropship_Model_Vendor $vendor */
		$vendor = $po->getVendor();
		$websiteId = $po->getStore()->getWebsiteId();
		$POSes = $vendor->getVendorPOSesPerWebsite($websiteId);

		if ($POSes->count() == 1) {
			return $POSes->getFirstItem();
		} else {
			$prodQtyFilter = array();
			/** @var Zolago_Po_Model_Po_Item $poItem */
			foreach ($po->getAllItemsTree() as $poItem) {
				$item		= $poItem->getChild() ? $poItem->getChild() : $poItem;
				$productId	= (int)$item->getData("product_id");
				$qty		= (int)$item->getData("qty");
				$prodQtyFilter[] = array(
					"product_id"	=> $productId,
					"qty"			=> $qty
				);
			}
			/** @var Zolago_Pos_Model_Pos $pos */
			foreach ($POSes as $pos) {
				/** @var Zolago_Pos_Model_Resource_Stock_Collection $stockColl */
				$stockColl = Mage::getResourceModel("zolagopos/stock_collection");
				$stockColl->addPosFilter($pos);
				$stockColl->addProductQtyFilter($prodQtyFilter);

				if ($stockColl->count() == count($prodQtyFilter)) {
					// we have winner!
					return $pos;
				}
			}
			return false; // No best pos
		}
	}
}

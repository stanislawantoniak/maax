<?php
class Zolago_Pos_Model_Observer {

	const ZOLAGO_POS_ASSIGN_APPROPRIATE_PO_POS_LIMIT = 100;
	
	public function udpoOrderSaveBefore($observer) { // After
		$udpos = $observer->getUdpos();
		foreach($udpos as $udpo){
			/* @var $udpo Unirgy_DropshipPo_Model_Po */
			$this->_assignPosToPo($udpo);
		}
		
	}
	
	protected function _assignPosToPo($udpo) {
		/* @var $udpo Unirgy_DropshipPo_Model_Po */
		if(!$udpo->getId() && !$udpo->getDefaultPosId()){
			$vendor = $udpo->getVendor();
			$bestPos = $this->_getBestPosByVendor($vendor);
			if($bestPos){
				$udpo->setDefaultPosId($bestPos->getId());
				$udpo->setDefaultPosName($bestPos->getName());
			}
			
		}
	}

	/**
	 * @param $vendor
	 * @return bool|Varien_Object
	 */
	protected function _getBestPosByVendor($vendor)
	{
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagopos/pos_collection");
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($vendor);
		$collection->addActiveFilter();
		$collection->setOrder("priority", Varien_Data_Collection::SORT_ORDER_ASC);

		if ($collection->count() == 1)
			return $collection->getFirstItem();


		/**
		 * Leave POS assignment for cron
		 *
		 * @see Zolago_Pos_Model_Observer::setAppropriatePoPos()
		 */
		return FALSE;

	}

	protected function getVendorPOSes($vendorId){
		$vendor = Mage::getModel("udropship/vendor")->load($vendorId);
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagopos/pos_collection");
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($vendor);
		$collection->addActiveFilter();
		$collection->setOrder("priority", Varien_Data_Collection::SORT_ORDER_ASC);
		return $collection;
	}

    public function setAppropriatePoPos(){
		//1. Get POs for recalculate  POSes
		/* @var $vendor Zolago_Po_Model_Po */
		$collection = Mage::getModel("zolagopo/po")->getCollection();
		$collection->addFieldToFilter("default_pos_id", array("null" => TRUE));
		$collection->addFieldToFilter("udropship_status", array("nin" => array(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED)));
		$collection->setPageSize(self::ZOLAGO_POS_ASSIGN_APPROPRIATE_PO_POS_LIMIT);


		//2. Collect product that stock need to be analyzed
		// Collect only simple products data
		/**
		 *
		$data = array(
			"vendor_id_1" => array(
				"po_id_1" => array(
					"product_id_1" => array(
						"[skuv]" => "04P633-5-353_XXX",
						"[qty]" => "3"
					),
					"product_id_2" => array(
						"[skuv]" => "04P633-5-353_YYY",
						"[qty]" => "6"
					)
				),
				"po_id_2" => array(
					"product_id" => array(
						"[skuv]" => "04P633-5-353",
						"[qty]" => "3"
					)
				),
				...
			),

			...
		);
		 * */

		$data = array();

		//What we need
		$productIds = array();
		foreach ($collection as $po) {
			$udropshipVendor = $po->getData("udropship_vendor");

			foreach ($po->getAllItems() as $poItem) {

				$vendorSimpleSku = $poItem->getData("vendor_simple_sku");

				if (!empty($vendorSimpleSku)) {
					$data[$udropshipVendor][$po->getId()][$poItem->getData("product_id")] = array(
						"skuv" => $vendorSimpleSku,
						"qty" => (int)$poItem->getData("qty")
					);
					$productIds[$udropshipVendor][$poItem->getData("product_id")] = $vendorSimpleSku;
				}
				unset($parentItemId);
			}

		}

		//3. Get STOCK from converter (What we have)
		$converterHelper = Mage::helper("zolagoconverter");
		if (empty($productIds)) {
			//Nothing to recalculate
			return;
		}

		$posesToAssign = array();
		$qtysFromConverter = array(); //Collect qtys from converter


		$poses = array();

		foreach ($data as $vendorId => $dataPerPO) {
			$vendorPOSes = $this->getVendorPOSes($vendorId);

			//Hm Vendor doesn't have POSes!!!
			if ($vendorPOSes->count() == 0)
				continue;


			foreach ($dataPerPO as $poId => $dataPerProduct) {

				foreach ($vendorPOSes as $pos) {
					$poses[$pos->getId()] = $pos->getName();
					$goodPOS = array();
					foreach ($dataPerProduct as $id => $productDetails) {
						if (isset($qtysFromConverter[$pos->getExternalId()][$productDetails["skuv"]])) {
							$qtyFromConverter = $qtysFromConverter[$pos->getExternalId()][$productDetails["skuv"]];
						} else {
							$qtyFromConverter = (int)$converterHelper->getQty($vendorId, $pos, $productDetails["skuv"]);
						}

						$qtysFromConverter[$pos->getExternalId()][$productDetails["skuv"]] = $qtyFromConverter;

						if ($productDetails["qty"] <= $qtyFromConverter) {
							$goodPOS[] = 1;
							$qtysFromConverter[$pos->getExternalId()][$productDetails["skuv"]] = $qtyFromConverter-$productDetails["qty"];
						} else {
							$posesToAssign[$poId] = "PROBLEM_POS";
						}
					}
					if (count($goodPOS) == count($dataPerProduct)) {
						$posesToAssign[$poId] = $pos->getId();
						//We found good POS for PO, go to the next PO
						break;
					}
				}
				unset($qtyFromConverter);
			}
			unset($poId);

		}


		//4. Assign POSes

		//Nothing to assign
		if (empty($qtysFromConverter))
			return;

		$collectionPO = Mage::getModel("udropship/po")->getCollection();
		$collectionPO->addFieldToFilter("entity_id", array("in" => array_keys($posesToAssign)));

		foreach($collectionPO as $udpo){
			if($posesToAssign[$udpo->getData("entity_id")] == "PROBLEM_POS"){
			    $vendor = $udpo->getVendor();
			    $posId = $vendor->getProblemPosId();
			    if ($posId) {
			        $pos = Mage::getModel('zolagopos/pos')->load($posId);
			        $udpo->setDefaultPosId($posId);
			        $udpo->setDefaultPosName($pos->getName());
			        $udpo->save();
			    } else {
			        $posList = $this->getVendorPoses($vendor->getId());
			        $pos = $posList->getFirstItem();
			        if ($pos->getId()) {
    			        $udpo->setDefaultPosId($pos->getId());
	    		        $udpo->setDefaultPosName($pos->getName());
		    	        $udpo->save();
                    } // else no assigned pos
			    }
			} else {
				$udpo->setDefaultPosId($posesToAssign[$udpo->getId()]);
				$udpo->setDefaultPosName($poses[$posesToAssign[$udpo->getId()]]);
				$udpo->save();
			}
		}

    }
}

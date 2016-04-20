<?php

class Zolago_Rma_Model_Resource_Rma_Track_Collection extends ZolagoOs_Rma_Model_Mysql4_Rma_Track_Collection
{
	public function addVendorFilter() {
		return $this->addFieldToFilter("track_creator", 
			Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_VENDOR);
	}
	public function addCustomerFilter() {
		return $this->addFieldToFilter("track_creator", 
			Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER);
	}
}

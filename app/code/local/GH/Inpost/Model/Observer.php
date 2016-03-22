<?php

class GH_Inpost_Model_Observer {

	/**
	 * Retrieve all lockers from InPost Api
	 * Process it and update if needed
	 *
	 * @return $this
	 */
	public function updateAllLockers() {
		/** @var GH_Inpost_Model_Resource_Api $res */
		$res = Mage::getResourceModel("ghinpost/api");
		$res->updateAllLockers();
		return $this;
	}
}
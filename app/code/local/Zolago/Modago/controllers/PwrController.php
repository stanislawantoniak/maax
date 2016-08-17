<?php

class Zolago_Modago_PwrController extends Mage_Core_Controller_Front_Action {
	
	public function getPopulateMapDataAction() {
		$town = html_entity_decode(trim($this->getRequest()->getParam("town", "")));
		$result = array();

		/* @var $collection GH_Inpost_Model_Resource_Locker_Collection */
		$collection = Mage::getResourceModel("zospwr/point_collection");
		$collection->addFieldToFilter("town", array("like" => "%" . $town . "%"));
		$collection->addFieldToFilter("is_active", ZolagoOs_Pwr_Model_Point::STATUS_ACTIVE);
		$collection->addOrder("street", "ASC");

		$lockers = array();
		$streets = array();

		foreach ($collection as $point) {
			/* @var $point ZolagoOs_Pwr_Model_Point */
			$townName = (string)ucwords(strtolower($point->getTown()));

			$lockers[] = array(
				"id" => $point->getId(),
				"name" => $point->getName(),
				'street' => htmlentities(trim((string)$point->getStreet())),
				'town' => $townName,
				"location_description" => htmlentities(trim((string)$point->getLocationDescription())),
				"longitude" => $point->getLongitude(),
				"latitude" => $point->getLatitude()
			);

			$streets[$point->getName()] = trim((string)$point->getStreet());
		}
		if (!empty($lockers)) {
			$result["map_points"] = $lockers;
		}

		if (!empty($streets)) {
			$result["filters"] = $streets;
		}
		
		echo json_encode($result, JSON_HEX_APOS);
		exit;
	}
}
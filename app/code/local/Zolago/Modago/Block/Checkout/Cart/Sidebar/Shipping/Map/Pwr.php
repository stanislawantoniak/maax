<?php

class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map_Pwr
	extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping {

	public static function getPopulateMapData() {
		$result = array(
			'map_points' => "",
			'filters' => array(),
		);

		/** @var ZolagoOs_Pwr_Model_Resource_Point_Collection $collection */
		$collection = Mage::getResourceModel("zospwr/point_collection");
		$collection->addFieldToFilter("is_active", ZolagoOs_Pwr_Model_Point::STATUS_ACTIVE);
		$collection->addOrder("town", "ASC");

		if (!$collection->getFirstItem()->getId()) {
			return $result;
		}
		
		$lockers = array();
		$filters = array();

		foreach ($collection as $point) {
			/* @var $point ZolagoOs_Pwr_Model_Point */
			$townName = (string)ucwords(strtolower($point->getTown()));

			$filters[$townName][$point->getName()] = $point->getData();

			$additional = array(
				trim($point->getStreet() . " " . $point->getBuildingNumber()),
				trim($townName),
				"(" . trim($point->getLocationDescription()) . ")"
			);
			$lockers[] = array(
				"id" => $point->getId(),
				"name" => $point->getName(),
				'street' => htmlentities(trim($point->getStreet())),
				'town' => htmlentities($townName),
				"location_description" => htmlentities(trim((string)$point->getLocationDescription())),
				"longitude" => (string)$point->getLongitude(),
				"latitude" => (string)$point->getLatitude(),
				"additional" => htmlentities(implode("<br />", $additional))
			);
		}
		if (!empty($lockers)) {
			$result["map_points"] = str_replace('\\\\', '\\', json_encode($lockers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
		}

		if (!empty($filters)) {
			$result["filters"] = $filters;
		} else {
			$result["filters"] = array();
		}
		
		return $result;
	}

}
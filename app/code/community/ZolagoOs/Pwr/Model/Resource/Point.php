<?php

/**
 * Class ZolagoOs_Pwr_Model_Resource_Point
 */
class ZolagoOs_Pwr_Model_Resource_Point extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zospwr/point', 'id');
	}

	public function updateAllPoints() {
		/** @var ZolagoOs_Pwr_Model_Resource_Point_Collection $collection */
		$collection = Mage::getResourceModel("zospwr/point_collection");
		$collection->load();
		/* @var $transaction Varien_Db_Adapter_Interface */
		$transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
		try {
			$transaction->beginTransaction();
			
			/** @var Orba_Shipping_Model_Packstation_Client_Pwr $client */
			$client = Mage::getModel('orbashipping/packstation_client_pwr');
			$array = $client->giveMeAllRUCH();
			
			$updatesIds = array();
			foreach ($array as $point) {
				$data = $this->processDataFromListmachines($point);
				/** @var ZolagoOs_Pwr_Model_Point|null $point */
				$point = $collection->getItemByColumnValue('name', $data['name']);
				if (is_null($point)) {
					// New point if don't exist
					$point = Mage::getModel("zospwr/point");
				}
				$point->addData($data);
				$point->save();
				$updatesIds[] = $point->getId();
			}
			// Set not active for no longer existing lockers
			/** @var ZolagoOs_Pwr_Model_Resource_Point_Collection $collection */
			$collection = Mage::getResourceModel("zospwr/point_collection");
			$collection->addFieldToFilter('id', array('nin' => $updatesIds));
			foreach ($collection as $locker) {
				/** @var GH_Inpost_Model_Locker $locker */
				$locker->setIsActive(ZolagoOs_Pwr_Model_Point::STATUS_INACTIVE);
				$locker->save();
			}

			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
			Mage::logException($e);
		}
		return $this;
	}

	/**
	 * Do stuff
	 *
	 * @param $data
	 * @return array
	 */
	protected function processDataFromListmachines($data) {
		$processed = array();
		foreach ($data as $key => $value) {
			$fieldName = $this->mapName($key);
			if (!empty($fieldName) && !(is_array($value) && empty($value))) {
				$processed[$fieldName] = trim($value);
			}

			if ($key == "CashOnDelivery") {
				if ($value == 'true') {
					$processed['payment_available']	= (string)ZolagoOs_Pwr_Model_Point::PAYMENT_AVAILABLE;
					$processed['payment_type']		= (string)ZolagoOs_Pwr_Model_Point::PAYMENT_TYPE_CASH;
				} else {
					$processed['payment_available']	= (string)ZolagoOs_Pwr_Model_Point::PAYMENT_NOT_AVAILABLE;
					$processed['payment_type']		= (string)ZolagoOs_Pwr_Model_Point::PAYMENT_TYPE_NOT_AVAILABLE;
				}
			}

			if ($key == "Zipcode") {
                $processed["postcode"] = $value;
            }
		}

		if (isset($processed['is_active']) && $processed['is_active'] == 'T') {
			$processed['is_active'] = (string)ZolagoOs_Pwr_Model_Point::STATUS_ACTIVE;
		} else {
			$processed['is_active'] = (string)ZolagoOs_Pwr_Model_Point::STATUS_INACTIVE;
		}
		return $processed;
	}

	/**
	 * Retrieve our object field name
	 *
	 * @param $inpostName
	 * @return string|null
	 */
	public function mapName($inpostName) {
		$map = $this->getNameMapperArray();
		$name = isset($map[$inpostName]) ? $map[$inpostName] : null;
		return $name;
	}

	/**
	 * Represent PwR fields
	 *
	 * @return array
	 */
	public function getNameMapperArray() {
		$map = array(
			// Mapped like in InPost names fields
			"DestinationCode"		=> "name",
			"StreetName"			=> "street",
			"BuildingNumber"		=> "building_number",
			"City"					=> "town",
			"Longitude"				=> "longitude",
			"Latitude"				=> "latitude",
			"Province"				=> "province",
			"OpeningHours"			=> "operating_hours",
			"Location"				=> "location_description",
			"PointType"				=> "type",
			// New fields
			"PSD" => "psd",
			"District"				=> "district", // same as city?
			// populated by mix of data
			"Available"				=> "is_active",
            "postcode"				=> "postcode",
			//"CashOnDelivery"		=> "payment_available",
			//"CashOnDelivery"		=> "payment_type",
			// And this do not exist
			/*
			'status'				=> 'status',
			'locationDescription2'	=> 'location_description2',
			'paymentpointdescr'		=> 'payment_point_description',
			'partnerid'				=> 'partner_id',
			*/
		);
		return $map;
	}
}
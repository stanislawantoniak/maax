<?php

class GH_Inpost_Model_Resource_Api extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('ghinpost/locker', 'id');
	}

	public function updateAllLockers() {
		/** @var GH_Inpost_Model_Resource_Locker_Collection $collection */
		$collection = Mage::getResourceModel("ghinpost/locker_collection");
		$collection->load();
		/* @var $transaction Varien_Db_Adapter_Interface */
		$transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
		try {
			$transaction->beginTransaction();

			$settings = Mage::helper('ghinpost')->getApiSettings(null,null);
			/** @var Orba_Shipping_Model_Packstation_Client_Inpost $client */
			$client = Mage::getModel('orbashipping/packstation_client_inpost');
			$client->setShipmentSettings($settings);
			$array = $client->getListMachines();
			
			$updatesIds = array();
			foreach ($array as $root) {
				foreach ($root as $machines) {
					$data = $this->processDataFromListmachines($machines);
					$locker = $collection->getItemByColumnValue('name', $data['name']);
					if (is_null($locker)) {
						// New locker if don't exist
						$locker = Mage::getModel("ghinpost/locker");
					}
					$locker->addData($data);
					$locker->save();
					$updatesIds[] = $locker->getId();
				}
			}
			// Set not active for no longer existing lockers
			/** @var GH_Inpost_Model_Resource_Locker_Collection $collection */
			$collection = Mage::getResourceModel("ghinpost/locker_collection");
			$collection->addFieldToFilter('id', array('nin' => $updatesIds));
			foreach ($collection as $locker) {
				/** @var GH_Inpost_Model_Locker $locker */
				$locker->setIsActive(0);
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
		}
		if (isset($processed['payment_available'])) {
			$processed['payment_available'] = (string)($processed['payment_available'] == 't' ?
				GH_Inpost_Model_Locker::PAYMENT_AVAILABLE :
				GH_Inpost_Model_Locker::PAYMENT_NOT_AVAILABLE);
		} else {
			$processed['payment_available'] = GH_Inpost_Model_Locker::PAYMENT_NOT_AVAILABLE;
		}
		$processed['is_active'] = "1";
		return $processed;
	}

	/**
	 * Retrieve our object field name from inpost field name
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
	 * Represent inpost api fields to GH_Inpost_Model_Locker fields
	 *
	 * @return array
	 */
	public function getNameMapperArray() {
		$map = array(
			'name'					=> 'name',
			'type'					=> 'type',
			'postcode'				=> 'postcode',
			'province'				=> 'province',
			'street'				=> 'street',
			'buildingnumber'		=> 'building_number',
			'town'					=> 'town',
			'latitude'				=> 'latitude',
			'longitude'				=> 'longitude',
			'paymentavailable'		=> 'payment_available',
			'status'				=> 'status',
			'locationdescription'	=> 'location_description',
			'locationDescription2'	=> 'location_description2',
			'operatinghours'		=> 'operating_hours',
			'paymentpointdescr'		=> 'payment_point_description',
			'partnerid'				=> 'partner_id',
			'paymenttype'			=> 'payment_type',
		);
		return $map;
	}
}
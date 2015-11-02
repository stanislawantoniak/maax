<?php

class GH_Integrator_Helper_Data extends Mage_Core_Helper_Abstract {
	const LOG_NAME = "GH_Integrator.log";
	const STATUS_ERROR = 'ERROR'; //GH_Integrator_Exception error - error that we've been expecting
	const STATUS_FATAL_ERROR = 'FATAL'; //internal magento error due to not expected event
	const STATUS_OK = 'OK';
	const FILE_DESCRIPTIONS = 'DESCRIPTIONS';
	const FILE_PRICES = 'PRICES';
	const FILE_STOCKS = 'STOCKS';

	/**
	 * returns array of timestamps based on description hours set in GH Integrator Settings
	 * @return array
	 */
	public function getDescriptionTimes() {
		return $this->valueToTimeArray(Mage::getStoreConfig('ghintegrator/hours/description'));
	}

	/**
	 * returns array of timestamps based on price hours set in GH Integrator Settings
	 * @return array
	 */
	public function getPriceTimes() {
		return $this->valueToTimeArray(Mage::getStoreConfig('ghintegrator/hours/price'));
	}

	/**
	 * returns array of timestamps based on stock hours set in GH Integrator Settings
	 * @return array
	 */
	public function getStockTimes() {
		return $this->valueToTimeArray(Mage::getStoreConfig('ghintegrator/hours/stock'));
	}

	/**
	 * validates hours provided in config and returns them in reverse order for today
	 * @param string $value
	 * @return array
	 */
	private function valueToTimeArray($value) {
		if($value) {
			$values = explode(',',$value);
			$return = array();
			foreach($values as $time) {
				if($currentTime = $this->getTime($time)) {
					$return[] = $currentTime;
				}
			}
			if(count($return)) {
				rsort($return);
				return $return;
			}
		}
		return array();
	}

	/**
	 * checks if provided string is correct hour
	 * supported formats are: from 00:00 (also 0:00) to 23:59
	 * if 0:00 (or other hour not starting with 0 like 9:12 or 4:25) is provided then adds 0 in front (so they become 09:12 and 04:25)
	 * then creates timestamps from them
	 * @param string $time
	 * @return bool
	 */
	private function getTime($time) {
		if (preg_match("/(2[0-3]|[01][0-9]|[0-9]):([0-5][0-9])/", $time)) {
			$timeArr = explode(":",$time);
			if(strlen($timeArr[0]) == 1) {
				$timeArr[0] = "0".$timeArr[0];
				$time = implode(":",$timeArr);
			}
			return strtotime($time);
		}
		return false;
	}

	/**
	 * logs GH Integrator events, in db and file
	 * @param string $log
	 * @param null|int $vendorId
	 * @returns GH_Integrator_Model_Log
	 */
	public function log($log,$vendorId=null) {
		Mage::log("Vendor ID: $vendorId | ".$log,null,self::LOG_NAME);

		/** @var GH_Integrator_Model_Log $logModel */
		$logModel = Mage::getModel('ghintegrator/log');
		if($vendorId) {
			$logModel->setVendorId($vendorId);
		}
		/** @var Mage_Core_Model_Date $dateModel */
		$dateModel = Mage::getModel('core/date');
		$logModel
			->setCreatedAt(date('Y-m-d H:i:s',$dateModel->gmtTimestamp()))
			->setLog($log)
			->save();

		return $logModel;
	}

	/**
	 * @param $msg
	 * @throws Mage_Core_Exception
	 */
	public static function throwException($msg) {
		throw Mage::exception("GH_Integrator",$msg);
	}
}
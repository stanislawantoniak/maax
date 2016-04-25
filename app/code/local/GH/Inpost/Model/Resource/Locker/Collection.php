<?php

class GH_Inpost_Model_Resource_Locker_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct() {
		$this->_init('ghinpost/locker');
	}

	/**
	 * add filter by name of locker
	 * 
	 * @param $name
	 * @return $this
	 */
	public function addLockerNameFilter($name) {
		$names = $name;
		if (!is_array($name)) {
			$names = array($name);
		}
		$this->addFieldToFilter('locker_name', array('in' => $names));
		return $this;
	}
}
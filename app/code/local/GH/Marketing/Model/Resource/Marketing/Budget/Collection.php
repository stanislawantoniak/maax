<?php

/**
 * Class GH_Marketing_Model_Resource_Marketing_Budget_Collection
 */
class GH_Marketing_Model_Resource_Marketing_Budget_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ghmarketing/marketing_budget');
    }

	/**
	 * @param $vendorId
	 * @return $this
	 */
	public function addVendorFilter($vendorId) {
		$this->addFieldToFilter('main_table.vendor_id', $vendorId);
		return $this;
	}

	/**
	 * Make between date filter
	 *
	 * @param string $month
	 * @return $this
	 */
	public function addMonthFilter($month) {
		$time = strtotime($month);
		$fromDate = date("Y-m-d 00:00:00", strtotime("first day of", $time));
		$toDate   = date("Y-m-d 23:59:59", strtotime("last day of", $time));
		$this->addFieldToFilter('main_table.date', array('from' => $fromDate, 'to' => $toDate));
		return $this;
	}
}
 

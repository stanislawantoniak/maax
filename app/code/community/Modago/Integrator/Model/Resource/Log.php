<?php

/**
 * Class Modago_Integrator_Model_Resource_Log
 */
class Modago_Integrator_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('modagointegrator/log', 'id');
    }

	/**
	 * Remove old logs from table
	 *
	 * @return $this
	 */
	public function removeOldLogs() {
		$write = $this->_getWriteAdapter();
		/** @var Mage_Core_Model_Date $cData */
		$cData = Mage::getModel('core/date');
		$offset = Mage::helper('modagointegrator/api')->getLogDays() * 24 * 60 * 60;
		$date = date('Y-m-d H:i:s', $cData->timestamp() - $offset);
		$write->delete($this->getMainTable(), "date < '" . $date ."'");
		return $this;
	}
}
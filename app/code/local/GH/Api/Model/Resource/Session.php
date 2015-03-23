<?php
class GH_Api_Model_Resource_Session extends Mage_Core_Model_Resource_Db_Abstract {
	public function _construct() {
		$this->_init('ghapi/session', "session_id");
	}

    /**
     * Removing Expired Sessions by array IDs
     *
     * @param $data
     */
    public function removeExpiredSessions($data) {
        if (!is_array($data) && is_numeric($data)) {
            $data = array($data);
        }
        if (!empty($data) && is_array($data)) {
            $write = $this->_getWriteAdapter();
            $write->delete($this->getSessionTable(), " `session_id` IN (".implode(",",$data).")");
        }
    }

    /**
     * Gets session table name
     *
     * @return mixed|string
     */
    protected  function getSessionTable() {
        return $this->getTable('ghapi/session');
    }
}
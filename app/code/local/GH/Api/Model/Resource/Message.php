<?php
class GH_Api_Model_Resource_Message extends Mage_Core_Model_Resource_Db_Abstract {
	public function _construct() {
		$this->_init('ghapi/message', "message_id");
	}

	/**
	 * removes messages from database
	 * param data is array of message ids to delete
	 * example: array(1,2,3,4,5,6,7,8)
	 * @param $data
	 */
	public function deleteMessages(array $data) {
		$write = $this->_getWriteAdapter();
		$write->delete($this->getMessageTable(),$this->getWhereInClause($data));
	}

	/**
	 * changes messages status to read
	 * param data is array of message ids to update
	 * example: array(1,2,3,4,5,6,7,8)
	 * @param array $data
	 */
	public function setMessagesAsRead(array $data) {
		$write = $this->_getWriteAdapter();
		$write->update(
			$this->getMessageTable(),
			array('status'=>GH_Api_Model_System_Source_Message_Status::GH_API_MESSAGE_STATUS_READ),
			$this->getWhereInClause($data)
		);
	}

	/**
	 * returns gh_api_model_message table name
	 * @return string
	 */
	protected function getMessageTable() {
		return $this->getTable('ghapi/message');
	}

	/**
	 * returns where clause based on ids
	 * @param array $data
	 * @return string
	 */
	protected function getWhereInClause(array $data) {
		return "`".$this->getIdFieldName()."` IN (".implode(",",$data).")";
	}
}
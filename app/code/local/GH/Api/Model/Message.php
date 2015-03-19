<?php

/**
 * Class GH_Api_Model_Message
 * @method GH_Api_Model_Resource_Message_Collection getCollection()
 * @method GH_Api_Model_Resource_Message getResource()
 * @method int getMessageId()
 * @method GH_Api_Model_Message setMessageId(int $id)
 * @method int getVendorId()
 * @method GH_Api_Model_Message setVendorId(int $vendorId)
 * @method string getPoIncrementId()
 * @method GH_Api_Model_Message setPoIncrementId(string $poIncrementId)
 * @method string getMessage()
 * @method GH_Api_Model_Message setMessage(string $message)
 * @method int getStatus()
 * @method GH_Api_Model_Message setStatus(int $status)
 * @method string getUpdatedAt()
 * @method GH_Api_Model_Message setUpdatedAt(string $updatedAt)
 */
class GH_Api_Model_Message extends Mage_Core_Model_Abstract {

	protected function _construct()
	{
		$this->_init('ghapi/session');
	}

	/**
	 * Adds message to queue only if
	 * no new messages with same po_increment_id, same message text and status new exists
	 * if message with same message text and po_increment_id but its status is read then it adds a new one
	 * if message with same message text, po_increment_id and status new exist then only update updated_at field
	 * @param Unirgy_Dropship_Model_Po $po
	 * @param string $message
	 * @return GH_Api_Model_Message
	 */
	public function addMessage(Unirgy_Dropship_Model_Po $po,$message) {
		if(!$this->validateMessage($message)) {
			$this->throwWrongMessageException();
		} elseif(!($po instanceof Unirgy_Dropship_Model_Po)) {
			Mage::throwException('Message could not be added because of wrong PO object');
		} else {
			$helper = $this->getHelper();
			$messages = $this
				->getCollection()
				->filterByPoIncrementId($po->getIncrementId())
				->filterByMessage($message)
				->filterByStatus(GH_Api_Model_System_Source_Message_Status::GH_API_MESSAGE_STATUS_NEW);

			if(!$messages->count()) {
				$this
					->setVendorId($po->getVendorId())
					->setPoIncrementId($po->getIncrementId())
					->setMessage($message)
					->setUpdatedAt($helper->getDate())
					->save();
			} else {
				$existingMessage = $messages->getFirstItem();
				$existingMessage
					->setUpdatedAt($helper->getDate())
					->save();
				$this->setData($existingMessage->getData());
			}
		}
		return $this;
	}

	/**
	 * Checks if provided message is one of correct types
	 * @param string $message
	 * @return bool
	 */
	protected function validateMessage($message) {
		if(
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_NEW_ORDER ||
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_CANCELLED_ORDER ||
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED ||
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_ITEMS_CHANGED ||
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_DELIVERY_DATA_CHANGED ||
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_INVOICE_ADDRESS_CHANGED ||
			$message == GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_STATUS_CHANGED
		) {
			return true;
		}
		return false;
	}

	/**
	 * Confirms user messages, selects from db only ids that user is allowed to delete
	 * by vendor_id and status
	 * @param string $token
	 * @param array $messages
	 * @return bool
	 */
	public function confirmMessages($token, array $messages) {
		$user = $this->getUserByToken($token);

		$messagesIds = array();
		foreach($messages as $messageId) {
			if(is_numeric($messageId)) {
				$messagesIds[] = $messageId;
			}
		}
		if(count($messagesIds)) {
			$messages = $this->getCollection();
			$messages
				->filterByIds($messagesIds)
				->filterByVendorId($user->getVendorId())
				->filterByStatus(GH_Api_Model_System_Source_Message_Status::GH_API_MESSAGE_STATUS_READ);

			if($messages->count()) {
				$validatedMessagesIds = array();
				foreach($messages as $key=>$message) {
					/** @var GH_Api_Model_Message $message */
					$validatedMessagesIds[] = $message->getId();
				}
				try {
					$this->getResource()->deleteMessages($validatedMessagesIds);
					return true;
				} catch(Exception $e) {
					Mage::throwException("DB Error occurred while removing messages (error: ".$e->getMessage().")");
				}
			}

		}
		return false;
	}


	/**
	 * @param $token
	 * @param $batchSize
	 * @param null $message
	 * @return array
	 * @throws Mage_Core_Exception
	 */
	public function getMessages($token,$batchSize,$message=null) {
		$user = $this->getUserByToken($token);

		//check if batch is correct
		if(is_numeric($batchSize) && $batchSize > 0) {
			$batchSize = $batchSize > $this->getMaxMessageBatchSize() ? $this->getMaxMessageBatchSize() : $batchSize;
		} else {
			Mage::throwException('Provided batch size is not correct');
		}

		//get messages collection
		$messages = $this
			->getCollection()
			->filterByVendorId($user->getVendorId());

		//set limit to batchSize
		$messages->getSelect()->limit($batchSize);

		//set filter by message
		if(!is_null($message)) {
			if($this->validateMessage($message)) {
				$messages->filterByMessage($message);
			} else {
				$this->throwWrongMessageException();
				return array();
			}
		}

		//if collection has items then proceed
		if($messages->count()) {
			$messageIdsToSetAsRead = array();
			$messagesToReturn = array();
			foreach($messages as $message) {
				/** @var GH_Api_Model_Message $message */

				//collect ids to set as read
				if($message->getStatus() == GH_Api_Model_System_Source_Message_Status::GH_API_MESSAGE_STATUS_NEW) {
					$messageIdsToSetAsRead[] = $message->getId();
				}

				//and collect return data
				$messagesToReturn[] = array(
					'messageID' => $message->getId(),
					'messageType' => $message->getMessage(),
					'orderID' => $message->getPoIncrementId()
				);
			}

			try {
				//set collected messages as read
				$this->getResource()->setMessagesAsRead($messageIdsToSetAsRead);

				//and return them
				return $messagesToReturn;
			} catch(Exception $e) {
				Mage::throwException("DB Error occurred while setting messages as read (error: ".$e->getMessage().")");
			}
		}

		return array();
	}

	/**
	 * Gets max number of messages that can be send at once
	 * @return int
	 */
	protected function getMaxMessageBatchSize() {
		return 100; //todo: add config for this
	}

	protected function throwWrongMessageException() {
		Mage::throwException('Wrong message type');
	}

	/**
	 * Gets main GH Api helper
	 * @return GH_Api_Helper_Data
	 */
	protected function getHelper() {
		return Mage::helper('ghapi');
	}

	/**
	 * @param string $token
	 * @return GH_Api_Model_User
	 */
	protected function getUserByToken($token) {
		return $this->getHelper()->getUserByToken($token);
	}
}
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
		$this->_init('ghapi/message');
	}

	/**
	 * Adds message to queue only if
	 * no new messages with same po_increment_id, same message text and status new exists
	 * if message with same message text and po_increment_id but its status is read then it adds a new one
	 * if message with same message text, po_increment_id and status new exist then only update updated_at field
	 * @param $po Zolago_Po_Model_Po
	 * @param string $message
	 * @return GH_Api_Model_Message
	 */
	public function addMessage($po,$message) {
	    if(Mage::registry('GHAPI')) return false ;	    // message not added from gh_api
	    
        if(!$this->validateMessage($message)) {
			return false;
		} elseif(!$this->isNoticeMessageActive($po->getVendor(), $message)) {
            return false;
        } elseif(!($po instanceof ZolagoOs_OmniChannelPo_Model_Po)) {
			Mage::throwException('Message could not be added because of wrong PO object');
		} else {
            $this->unsetData();
			$helper = $this->getHelper();
			$messages = $this
				->getCollection()
				->filterByPoIncrementId($po->getIncrementId())
				->filterByMessage($message)
				->filterByStatus(GH_Api_Model_System_Source_Message_Status::GH_API_MESSAGE_STATUS_NEW);

			if(!$messages->count()) {
				$this
					->setVendorId($po->getVendor()->getId())
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
		$this->throwWrongMessageException();
		return false;
	}

    /**
     * @param $vendor Zolago_Dropship_Model_Vendor
     * @param $messages @see GH_Api_Model_System_Source_Message_Type
     * @return bool
     */
    protected function isNoticeMessageActive($vendor, $messages) {
        switch ($messages) {
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_NEW_ORDER:
                return $vendor->getData('ghapi_message_new_order');
                break;
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_CANCELLED_ORDER:
                return $vendor->getData('ghapi_message_order_canceled');
                break;
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED:
                return $vendor->getData('ghapi_message_order_payment_changes');
                break;
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_ITEMS_CHANGED:
                return $vendor->getData('ghapi_message_order_product_changes');
                break;
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_DELIVERY_DATA_CHANGED:
                return $vendor->getData('ghapi_message_order_shipping_changes');
                break;
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_INVOICE_ADDRESS_CHANGED:
                return $vendor->getData('ghapi_message_order_invoice_changes');
                break;
            case GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_STATUS_CHANGED:
                return $vendor->getData('ghapi_message_order_status_changes');
                break;
        }
        return false; // Never
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
		$vendor = $user->getVendor();

		//first check if all provided ids are numbers
		$messagesIds = array();
		$notNumericMessagesIds = array();
		foreach($messages as $messageId) {
			if(is_numeric($messageId)) {
				$messagesIds[] = $messageId; //collect correct messagesIds
			} else {
				$notNumericMessagesIds[] = $messageId; //collect invalid messagesIds
			}
		}
		$messagesIdsCount = count($messagesIds);
		$notNumericMessagesIdsCount = count($notNumericMessagesIds);

		if($notNumericMessagesIdsCount) { //if there are not number ids in input data
			$this->throwMessageIdNotNumericError($notNumericMessagesIds);
		} elseif(!$messagesIdsCount) { //if there are no ids in input data
			$this->throwMessageIdEmptyError();
		} else {
			$messagesCollection = $this->getCollection();
			$messagesCollection->filterByIds($messagesIds); //get messages by inputed ids

			$messagesCollectionCount = $messagesCollection->count();

			if(!$messagesCollectionCount) { //if there are no messages in db
				$this->throwMessageIdWrongError($messagesIds);
			} else {
				$invalidVendorMessagesIds = array();
				$invalidStatusMessagesIds = array();
				$messagesIdsToDelete = array();
				foreach($messagesCollection as $message) {
					/** @var GH_Api_Model_Message $message */
					if($message->getVendorId() != $user->getVendorId()) { //messages for another vendors
						$invalidVendorMessagesIds[] = $message->getId();
					} elseif($message->getStatus() != GH_Api_Model_System_Source_Message_Status::GH_API_MESSAGE_STATUS_READ) { //not read messages
						$invalidStatusMessagesIds[] = $message->getId();
					} else { //good messages
						$messagesIdsToDelete[] = $message->getId();
					}
				}

                if(count($invalidStatusMessagesIds)) { //throw error if user tried to confirm not read messages
                    $this->throwMessageIdStatusInvalidError($invalidStatusMessagesIds);
                }
                if(count($invalidVendorMessagesIds)) { //throw error if user provided messages ids that are not his
					$this->throwMessageIdWrongError($invalidVendorMessagesIds);
				}
                $idsCheck = array_diff($messagesIds, $messagesIdsToDelete);
                if(count($idsCheck)) {
                    // throw error if there is still deference between ids to confirm (deleting in DB)
                    // and collected ids gets from collection
                    $this->throwMessageIdWrongError($idsCheck);
                }

				//set reservation flag to 0 if config says so
				if($vendor->getData('ghapi_reservation_disabled')) {
					$posToChange = $this->getPosIdsByNewOrderMessages($messagesIdsToDelete);
					if(count($posToChange)) {
						/** @var Zolago_Po_Model_Po $poModel */
						$poModel = Mage::getModel('zolagopo/po');
						$poModel->ghApiSetOrdersReservationAfterRead($posToChange);
					}
				}

				//confirm messages
                try {
                    $this->getResource()->deleteMessages($messagesIdsToDelete);
                    return true;
                } catch(Exception $e) {
                    $this->getHelper()->throwDbError(); //throw db error
                }

			}
		}
		return false;
	}

	/**
	 * @param array $messagesIds
	 * @return array
	 */
	protected function getPosIdsByNewOrderMessages($messagesIds) {
		$messagesCollection = $this->getCollection();
		$messagesCollection->filterByIds($messagesIds);
		$messagesCollection->filterByMessage(GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_NEW_ORDER);
		$posIds = array();
		/** @var GH_Api_Model_Message $message */
		foreach($messagesCollection as $message) {
			$posIds[] = $message->getPoIncrementId();
		}
		return $posIds;
	}

	/**
	 * @param array $messagesIds
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwMessageIdNotNumericError(array $messagesIds) {
		Mage::throwException('error_message_id_not_numeric ('.implode(',',$messagesIds).')');
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwMessageIdEmptyError() {
		Mage::throwException('error_message_id_empty');
	}

	/**
	 * @param array $messagesIds
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwMessageIdWrongError(array $messagesIds = array()) {
		$ids = count($messagesIds) ? ' ('.implode(',',$messagesIds).')' : '';
		Mage::throwException('error_message_id_wrong'.$ids);
	}

	/**
	 * @param array $messagesIds
	 * @throws Mage_Core_Exception
	 */
	protected function throwMessageIdStatusInvalidError(array $messagesIds) {
		Mage::throwException('error_message_not_read ('.implode(',',$messagesIds).')');
	}

	/**
	 * @param $token
	 * @param $batchSize
	 * @param null $message
	 * @param null $orderId
	 * @return array
	 * @throws Mage_Core_Exception
	 */
	public function getMessages($token,$batchSize,$message=null,$orderId = null) {
		$user = $this->getUserByToken($token);

		//check if batch is correct
		if(!(is_numeric($batchSize) && $batchSize <= $this->getMaxMessageBatchSize() && ($batchSize > 0))) {
			Mage::throwException('error_message_batchsize_invalid (min: 1, max: ' . $this->getMaxMessageBatchSize() . ")");
		}

		//get messages collection
		$messages = $this
			->getCollection()
			->filterByVendorId($user->getVendorId())
			->setOrder('po_increment_id','DESC')
			->setOrder('message_id','ASC');
        // order filter
        if ($orderId) {
			$messages->filterByOrderId($orderId);
        };
			

		//set limit to batchSize
		$messages->getSelect()->limit($batchSize);

		//set filter by message
		if(!is_null($message) && $message) {
			if($this->validateMessage($message)) {
				$messages->filterByMessage($message);
			} else {
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
                if(count($messageIdsToSetAsRead)) {
				    $this->getResource()->setMessagesAsRead($messageIdsToSetAsRead);
                }

				//and return them
				return $messagesToReturn;
			} catch(Exception $e) {
				$this->getHelper()->throwDbError();
			}
		}

		return array();
	}

	/**
	 * Gets max number of messages that can be send at once
	 * @return int
	 */
	protected function getMaxMessageBatchSize() {
		return Mage::getStoreConfig('ghapi_options/ghapi_messages/ghapi_message_batch_size');
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
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
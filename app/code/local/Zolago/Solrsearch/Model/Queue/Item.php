<?php

/**
 * Class Zolago_Solrsearch_Model_Queue_Item
 *
 * @method string getQueueId()
 * @method string getProductId()
 * @method string getStoreId()
 * @method string getStatus()
 * @method string getCoreName()
 * @method string getProcessedAt()
 * @method string getCreatedAt()
 * @method string getDeleteOnly()
 */
class Zolago_Solrsearch_Model_Queue_Item extends Mage_Core_Model_Abstract {

	const STATUS_WAIT = "wait";
	const STATUS_PROCESSING = "processing";
	const STATUS_DONE = "done";
	const STATUS_FAIL = "fail";
	
	public function _construct() {
        $this->_init('zolagosolrsearch/queue_item');
    }
	
}
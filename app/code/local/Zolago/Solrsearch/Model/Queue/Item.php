<?php
class Zolago_Solrsearch_Model_Queue_Item extends Mage_Core_Model_Abstract{
		
	const STATUS_WAIT = "wait";
	const STATUS_PROCESSING = "processing";
	const STATUS_DONE = "done";
	const STATUS_FAIL = "fail";
	
	public function _construct() {
        $this->_init('zolagosolrsearch/queue_item');
    }
	
}
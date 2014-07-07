<?php

class Zolago_Solrsearch_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Template {
	
	/**
	 * @return Zolago_Solrsearch_Model_Queue
	 */
	public function getQueue() {
		return Mage::getSingleton('zolagosolrsearch/queue');
	}
}
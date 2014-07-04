<?php

require_once Mage::getModuleDir('controllers', "SolrBridge_Solrsearch") . 
	DS . "Adminhtml" . DS . "SolrsearchController.php";

class Zolago_Solrsearch_Adminhtml_SolrsearchController 
	extends SolrBridge_Solrsearch_Adminhtml_SolrsearchController {
	
	
	/**
	 * Render queue interface
	 */
	public function queueAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Process queue
	 */
	public function processQueueAction() {
		$queue = Mage::getSingleton('zolagosolrsearch/queue');
		/* @var $queue Zolago_Solrsearch_Model_Queue */
		$session = $this->_getSession();
		
		try{
			
			if($queue->isEmpty()){
				$session->addSuccess(
					Mage::helper("zolagosolrsearch")->__("Queue is empty")
				);
			}else{
				$queue->process();
				$cores = $queue->getProcessedCores();
				$items = $queue->getProcessedItems();

				$session->addSuccess(
					Mage::helper("zolagosolrsearch")->__("Queue has been processed (%s cores, %d items)", $cores, $items)
				);
			}
			
		} catch (Exception $ex) {
			Mage::log($ex);
			
		}
		

		return $this->_redirect("*/*/queue");
	}
}
?>
<?php

require_once Mage::getModuleDir('controllers', "SolrBridge_Solrsearch") . 
	DS . "Adminhtml" . DS . "SolrsearchController.php";

class Zolago_Solrsearch_Adminhtml_SolrsearchController 
	extends SolrBridge_Solrsearch_Adminhtml_SolrsearchController {
	
	/**
	 * Process queue
	 */
	public function processQueueAction() {
		echo "process";
	}
}
?>
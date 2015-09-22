<?php
/**
 * override idiotic ordering
 */
class Zolago_Solrsearch_Block_Adminhtml_Logs extends SolrBridge_Solrsearch_Block_Adminhtml_Logs {
    
	public function getLogs() {
		$resource = Mage::getSingleton ( 'core/resource' );
		
		$logtable = $this->ultility->getLogTable ();
		
		$query = 'SELECT * FROM ' . $logtable . ' WHERE 1 ORDER BY `update_at` DESC ,`logs_id` DESC LIMIT 1000';
		
		$result = $this->ultility->getReadConnection ()->query ( $query );
		return $result->fetchAll ();
	}

    
}
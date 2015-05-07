<?php
/**
 * get rewrite link for filters
 */
class GH_Rewrite_Model_Rewrite extends Mage_Core_Model_Url_Rewrite {
    
    public function loadByRequestPathForFilters($id,$rawUrl) {
        return $this->_getResource()->loadByRequestPathForFilters($id, $rawUrl);        
    }
}
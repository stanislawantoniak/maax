<?php
/**
 * get rewrite link for filters
 *
 * @method GH_Rewrite_Model_Resource_Rewrite _getResource()
 */
class GH_Rewrite_Model_Rewrite extends Mage_Core_Model_Url_Rewrite {
    
    public function loadByRequestPathForFilters($id,$rawUrl) {
        return $this->_getResource()->loadByRequestPathForFilters($id, $rawUrl);        
    }
}
<?php

/**
 * display query results grid
 *
 * Class Gh_Common_Block_Adminhtml_Rawsql_Launch_Result
 */
class GH_Common_Block_Adminhtml_Rawsql_Launch_Result extends Mage_Adminhtml_Block_Widget {
    
    /**
     * query results or error from registry
     *
     * @return array
     */


    protected function _getResult() {
        return Mage::registry('ghcommon_query_result');
        
    }
    
    /**
     * query model from registry
     *
     * return GH_Common_Model_Sql
     */

    protected function _getQuery() {
        return Mage::registry('ghcommon_sql');
    }
}

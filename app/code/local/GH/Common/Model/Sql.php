<?php

/**
 * raw query model
 */
class GH_Common_Model_Sql extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('ghcommon/sql');
    }
    
    
    /**
     * launch raw sql query
     *
     * @return array
     */
     public function launchQuery() {
         $query = $this->getQueryText();         
         $resource = Mage::getSingleton('core/resource');
         $readConnection = $resource->getConnection('core_read');
         $readConnection->beginTransaction();
         $result = array();
         try {
             $readConnection->query($query);
             $result = $readConnection->fetchAll($query);
         } catch (Exception $e) {                      
             $result[] = array( 'error' => $e->getMessage());
         }
         $readConnection->rollback();
         return $result;
     }
}
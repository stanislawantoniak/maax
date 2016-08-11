<?php

/**
 * Resource for statement
 */
class GH_Statements_Model_Resource_Statement extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/statement', 'id');
    }


    /**
     * @param Mage_Core_Model_Abstract $object
     * @return GH_Statements_Model_Statement
     */
    public function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        return parent::_afterDelete($object);
    }

}
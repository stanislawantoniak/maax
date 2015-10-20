<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Document_Vendor_Collection
 */
class GH_Regulation_Model_Resource_Regulation_Document_Vendor_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_document_vendor');
    }

}
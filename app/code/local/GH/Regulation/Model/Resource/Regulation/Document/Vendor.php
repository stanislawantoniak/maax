<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Document_Vendor
 */
class GH_Regulation_Model_Resource_Regulation_Document_Vendor extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_document_vendor', 'id');
    }

}
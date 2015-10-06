<?php

/**
 * Class GH_Regulation_Model_Resource_RegulationDocumentVendor
 */
class GH_Regulation_Model_Resource_RegulationDocumentVendor extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_document_vendor', 'id');
    }

}
<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Document
 */
class GH_Regulation_Model_Resource_Regulation_Document extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_document', 'id');
    }

}
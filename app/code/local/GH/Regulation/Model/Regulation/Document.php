<?php

/**
 * Class GH_Regulation_Model_Regulation_Document
 * @method string getId()
 * @method string getRegulationTypeId()
 * @method string getDocumentLink()
 * @method string getDate()
 */
class GH_Regulation_Model_Regulation_Document extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_document');
    }

    public function getFileName() {
        return $this->getDocumentLink();// TODO
    }
}
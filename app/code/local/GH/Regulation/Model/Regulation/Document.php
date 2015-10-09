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
        if (!$this->hasData("file_name")) {
            $data = unserialize($this->getDocumentLink());
            if ($data) {
                $this->addData($data);
            }
        }
        return $this->getData('file_name');
    }

    public function getPath() {
        if (!$this->hasData("path")) {
            $data = unserialize($this->getDocumentLink());
            if ($data) {
                $this->addData($data);
            }
        }
        return $this->getData('path');
    }

    public function getAdminUrl($documentId = null) {
        if(is_null($documentId)) {
            $documentId = $this->getId();
        }
        return Mage::helper("adminhtml")->getUrl("adminhtml/regulation/getDocument",array('id'=>$documentId));
    }

    public function getVendorUrl($documentId = null) {
        if(is_null($documentId)) {
            $documentId = $this->getId();
        }
        return Mage::getUrl('dropship/regulation/getDocument',array('id'=>$documentId));
    }
}
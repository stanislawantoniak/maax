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

    /**
     * Get cleaned file name
     *
     * @return mixed
     */
    public function getFileName() {
        if (!$this->hasData("file_name")) {
            $data = unserialize($this->getDocumentLink());
            if ($data) {
                $this->addData($data);
            }
        }
        return $this->getData('file_name');
    }

    /**
     * Get relative path to file (without media dir)
     *
     * @return mixed
     */
    public function getPath() {
        if (!$this->hasData("path")) {
            $data = unserialize($this->getDocumentLink());
            if ($data) {
                $this->addData($data);
            }
        }
        return $this->getData('path');
    }

    /**
     * Get full path to file
     *
     * @return string
     */
    public function getFullPath() {
        return Mage::getBaseDir('media') . DS . $this->getPath();
    }

    /**
     * Get url for regulation document for magento admin
     *
     * @param null $documentId
     * @return mixed
     */
    public function getAdminUrl($documentId = null) {
        if(is_null($documentId)) {
            $documentId = $this->getId();
        }
        return Mage::helper("adminhtml")->getUrl("adminhtml/regulation/getDocument",array('id'=>$documentId));
    }

    /**
     * Get url for regulation document for valid vendor
     *
     * @param null $documentId
     * @return string
     */
    public function getVendorUrl($documentId = null)
    {
        if (is_null($documentId)) {
            $documentId = $this->getId();
        }
        return Mage::getUrl('udropship/vendor/getDocument', array('id' => $documentId));
    }

    /**
     * Get url for regulation document when vendor not jet active
     *
     * @param $token
     * @param null $documentId
     * @param null $vendorId
     * @return string
     */
    public function getVendorUrlByToken($token, $documentId = null, $vendorId = null) {
        if (is_null($documentId)) {
            $documentId = $this->getId();
        }
        return Mage::getUrl('udropship/vendor/getDocumentByToken', array('id' => $documentId, 'token' => $token, 'vendor' => $vendorId));
    }
}
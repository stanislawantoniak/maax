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
     * Get path to file
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

    /**
     * @return GH_Regulation_Model_Resource_Regulation_Document_Collection
     */
    public function getAcceptDocumentsList()
    {
        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i:s", $localeTime);

        $collection = $this->getCollection();
        $collection->getSelect()
            ->join(
                array('regulation_type' => 'gh_regulation_type'),
                'main_table.regulation_type_id = regulation_type.regulation_type_id')
            ->join(
                array('regulation_vendor_kind' => 'gh_regulation_vendor_kind'),
                'regulation_type.regulation_kind_id = regulation_vendor_kind.regulation_kind_id'
                )
            ->join(
                array('regulation_kind' => 'gh_regulation_kind'),
                'regulation_vendor_kind.regulation_kind_id = regulation_kind.regulation_kind_id',
                array("*")
            )
            ->group("regulation_vendor_kind.regulation_kind_id")
            ->where("main_table.date<=?", $localeTimeF);
        return $collection;
    }
}
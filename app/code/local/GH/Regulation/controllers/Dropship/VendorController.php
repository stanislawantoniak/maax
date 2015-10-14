<?php

/**
 * Flow:
 * TODO: write flow description + put this on wiki
 *
 * Class GH_Regulation_VendorController
 */
class GH_Regulation_Dropship_VendorController
    extends Zolago_Dropship_Controller_Vendor_Abstract
{
    /**
     * This is regulation document accept page for not jet active vendor
     * It's show only when data vendor is valid
     * and token it's not expired
     */
    public function acceptAction()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);   // Vendor id
            $key = $this->getRequest()->getParam('key', false); // Token

            if (empty($id) || empty($key)) {
                throw new Exception($this->__('Bad request.'));
            }
            try {

                /* @var $vendor Unirgy_Dropship_Model_Vendor */
                $vendor = Mage::getModel('udropship/vendor')->load($id);


                if ((!$vendor) || (!$vendor->getId())) {
                    throw new Exception('Failed to load vendor by id.');
                }
                if ($vendor->getConfirmation() !== $key) {
                    throw new Exception($this->__('Wrong confirmation key.'));
                }
                //Check if token not expired

                if ($vendor->getConfirmation() && $vendor->getConfirmationSent()) {
                    /* Vendor account confirmation token life time */
                    $confirmationTokenExpirationTime = Mage::getStoreConfig('udropship/microsite/confirmation_token_expiration_time');

                    $localeTime = Mage::getModel('core/date')->timestamp(time());
                    $secPastSinceConfirmation = $localeTime - strtotime($vendor->getConfirmationSentDate());
                    $hoursPastSinceConfirmation = $secPastSinceConfirmation / 60 / 60;

                    if ($hoursPastSinceConfirmation > $confirmationTokenExpirationTime) {
                        //If token expired redirect to cms page with info "Contact with GALLERY to get new confirmation email"

                        $this->_redirect('udropship/vendor/regulationexpired');
                        return;
                    }
                }
            } catch (Exception $e) {
                throw new Exception($this->__('Wrong vendor account specified.'));
            }
        } catch (Exception $e) {
            // die unhappy
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('udropship/vendor/');
            return;
        }
        return $this->_renderPage();
    }

    /**
     * Save information about that not jet active vendor
     * accept our regulation
     *
     * @throws Exception
     */
    public function acceptPostAction()
    {
        $req = $this->getRequest();
        $key = $req->getParam('key', false);
        $vendorId = $req->getPost('vendor', false);
        $acceptRegulations = $req->getPost('accept_regulations', false);
        $acceptRegulationsRole = $req->getPost('accept_regulations_role', false);
        $regulationDocumentNewName = $req->getPost('regulation_document_new_name', false);

        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);

        /** @var GH_Regulation_Helper_Data $_helper */
        $_helper = Mage::helper("ghregulation");

        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        if (empty($_POST)) {
            $this->_getSession()->addError($_helper->__("Security error"));
            return $this->_redirectReferer();
        }
        // Form key valid?
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $formKeyPost = $this->getRequest()->getParam('form_key');
        if ($formKey != $formKeyPost) {
            return $this->_redirectReferer();
        }

        if (!$vendorId) {
            $this->_getSession()->addError($_helper->__("Undefined vendor"));
            return $this->_redirectReferer();
        }

        if (!$acceptRegulations) {
            $this->_getSession()->addError($_helper->__("Please check Accept Regulation checkbox"));
            return $this->_redirectReferer();
        }

        if (isset($_FILES["regulation_document"]) && !empty($_FILES["regulation_document"])) {
            $allowedRegulationDocumentTypes = Mage::helper("ghregulation")->getAllowedRegulationDocumentTypes();
            $file = $_FILES["regulation_document"];

            $name = $file["name"];
            $type = $file["type"];
            $size = $file["size"];

            if (!in_array($type, $allowedRegulationDocumentTypes)) {
                $this->_getSession()->addError($_helper->__("File must be JPG, PNG or PDF"));
                return $this->_redirectReferer();
            }

            if (round($size / (1024 * 1024), 1) >= GH_Regulation_Helper_Data::REGULATION_DOCUMENT_MAX_SIZE) { //5MB
                $this->_getSession()->addError($_helper->__("File too large. File must be less than %sMB.", GH_Regulation_Helper_Data::REGULATION_DOCUMENT_MAX_SIZE));
                return $this->_redirectReferer();
            }

        }

        // activate customer
        if ($vendor->getConfirmation() !== $key) {
            throw new Exception($this->__('Wrong confirmation key.'));
        }

        $newName = $regulationDocumentNewName;
        $image = md5($newName);
        $safeFolderPath = $image[0] . "/" . $image[1] . "/";
        $folder = GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER . DS . "accept_" . (int)$vendorId;

        try {
            $vendor->setConfirmation(null);

            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $localeTimeF = date("Y-m-d H:i:s", $localeTime);

            $vendor->setData("regulation_accept_document_date", $localeTimeF);

            $vendor->setData("regulation_accepted", 1);

            $ghRegulationAcceptDocumentData = array(
                "IP" => $_SERVER['REMOTE_ADDR'],
                "document" => Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath . $newName,
                "accept_regulations_role" => $acceptRegulationsRole,
                "accept_regulations" => isset($_POST['accept_regulations']) ? 1 : 0
            );

            $vendor->setData("regulation_accept_document_data", json_encode($ghRegulationAcceptDocumentData));

            Mage::getResourceSingleton('udropship/helper')
                ->updateModelFields(
                    $vendor,
                    array(
                        'confirmation',
                        "regulation_accept_document_date",
                        "regulation_accept_document_data",
                        "regulation_accepted"
                    )
                );

        } catch (Exception $e) {
            throw new Exception($this->__('Failed to confirm vendor account.'));
        }

        $acceptAttachments = array();
        //uploaded document by vendor
        if ($regulationDocumentNewName && !empty($regulationDocumentNewName)) {

            $acceptAttachments[] = array(
                'filename' => $name,
                'content' => file_get_contents(Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath . $newName),
                'type' => $type,
            );
        }
        //our documents
        /** @var GH_Regulation_Model_Resource_Regulation_Document $docModel */
        $docModel = Mage::getResourceModel("ghregulation/regulation_document");
        $docs = $docModel->getDocumentsToAccept($vendor);

        if (count($docs) > 0) {
            /** @var GH_Regulation_Model_Regulation_Document $doc */
            foreach ($docs as $doc) {
                $acceptAttachments[] = array(
                    'filename' => $doc->getFileName(),
                    'content' => file_get_contents($doc->getPath()),
                    'type' => mime_content_type($doc->getPath()),
                );
            }
        }
        if (!empty($acceptAttachments)) {
            $vendor->setData("accept_attachments", $acceptAttachments);
        }

        //send Accept email
        Mage::helper('umicrosite')->sendVendorRegulationAcceptedEmail($vendor);
        $this->_redirect('udropship/vendor/regulationaccepted');
        return;
    }



    public function getDocumentAction()
    {
        $documentId = $this->getRequest()->getParam('id');
        if ($documentId) {
            /** @var Gh_Regulation_Model_Regulation_Document $document */
            $document = Mage::getModel('ghregulation/regulation_document')->load($documentId);
            /** @var GH_Regulation_Helper_Data $helper */
            $helper = Mage::helper('ghregulation');

            /** @var Zolago_Dropship_Model_Session $vendorSession */
            $vendorSession = Mage::getSingleton('udropship/session');

            $vendor = $vendorSession->getVendor();

            if ($document->getId() //document exists
                && $vendor->getId() //vendor is logged in
                && in_array($document->getId(),$helper->getVendorDocuments($vendor->getId(),true)) //vendor has rights to provided document
            ) {
                $path = $document->getPath();
                if (is_file($path) && is_readable($path)) {
                    $this->_sendFile($path, $document->getFileName());
                    return;
                }
            }
        }
        $this->norouteAction(); //404
        return;
    }

    /**
     * Get regulation document for not jet active vendor
     */
    public function getDocumentByTokenAction() {
        $req = $this->getRequest();
        $vendorId       = $req->getParam('vendor');
        $token          = $req->getParam('token');
        //$documentId     = $req->getParam('id'); //its read by $this->getDocumentAction()

        if (!empty($vendorId) && !empty($token)) { //vendor id and token was provided
            /* @var $vendor Zolago_Dropship_Model_Vendor */
            $vendor = Mage::getModel('udropship/vendor')->load($vendorId);

            if ($vendor && $vendor->getId() && $vendor->getConfirmation() === $token) { //correct vendor id and its token

                if ($vendor->getConfirmationSent()) { //vendor has received confirmation email
                    /* Vendor account confirmation token life time */
                    $confirmationTokenExpirationTime = Mage::getStoreConfig('udropship/microsite/confirmation_token_expiration_time');

                    $localeTime = Mage::getModel('core/date')->timestamp(time());
                    $secPastSinceConfirmation = $localeTime - strtotime($vendor->getConfirmationSentDate());
                    $hoursPastSinceConfirmation = $secPastSinceConfirmation / 60 / 60;

                    if ($hoursPastSinceConfirmation < $confirmationTokenExpirationTime) {
                        //If token is not expired proceed with document download
                        $this->getDocumentAction();
                        return;
                    }
                }
            }
        }
        $this->norouteAction();
    }

    public function getVendorUploadedDocumentAction() {
        $req = $this->getRequest();
        $fileName = $req->getParam('file', false);
        $vendorId = $req->getParam('vendor', false);
        $key    = $req->getParam('key', false); // Token

        // If no vendor ID maybe vendor is logged in
        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        if (!$vendor->getId()) {
            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $vendor = $this->_getSession()->getVendor();
            $vendorId = $vendor->getId();
        }

        if (!empty($fileName) || $vendorId) {

            /* Vendor account confirmation token life time */
            $confirmationTokenExpirationTime = Mage::getStoreConfig('udropship/microsite/confirmation_token_expiration_time');

            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $secPastSinceConfirmation = $localeTime - strtotime($vendor->getConfirmationSentDate());
            $hoursPastSinceConfirmation = $secPastSinceConfirmation / 60 / 60;

            if ($hoursPastSinceConfirmation < $confirmationTokenExpirationTime && !$this->_getSession()->isLoggedIn() && $vendor->getConfirmation() == $key
                || $this->_getSession()->isLoggedIn()) {

                $path  = Mage::getBaseDir('media') . DS . GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER . DS . "accept_" . (int)$vendorId . DS;
                $image = md5($fileName);
                $path .= $image[0] . "/" . $image[1] . "/" . $fileName;
                if (is_file($path) && is_readable($path)) {
                    $this->_sendFile($path, $fileName);
                    return;
                }
            }
        }
        $this->norouteAction(); //404
        return;
    }

    protected function _sendFile($filepath, $filename = null)
    {
        $filename = is_null($filename) ? basename($filepath) : $filename;

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            //->setHeader ( 'Content-type', 'application/pdf', true ) /*  View in browser */
            ->setHeader('Content-type', 'application/force-download')/*  Download        */
            ->setHeader('Content-Length', filesize($filepath))
            ->setHeader('Content-Disposition', 'inline' . '; filename=' . $filename);
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($filepath);
    }

    /**
     * Delete accept directory for vendor
     * @param $dir
     * @return bool
     */
    function deleteDirectory($dir)
    {
        try {
            system('rm -rf ' . escapeshellarg($dir), $retval);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $retval == 0; // UNIX commands return zero on success
    }

    /**
     * Save vendor document bt AJAX
     */
    public function saveVendorDocumentPostAction()
    {
        /** @var GH_Regulation_Helper_Data $helper */
        $helper = Mage::helper("ghregulation");

        $req = $this->getRequest();
        $vendorId = $req->getParam('vendor', false);
        $key = $req->getParam('key', false);
        $errorFlag = false;

        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);

        $result = array(
            "status" => 0,
            "content" => array()
        );

        if (!$vendor->getId() || $vendor->getConfirmation() != $key) {
            $errorFlag = true;
            $result['content'] = $helper->__("Security issue: you have no access for this action");
        }

        if (isset($_FILES["regulation_document"]) && !empty($_FILES["regulation_document"]) && !$errorFlag) {
            $folder = GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER . DS . "accept_" . (int)$vendorId;

            $dirname = Mage::getBaseDir('media') . DS . $folder . DS;
            $this->deleteDirectory($dirname);
            $allowedRegulationDocumentTypes = $helper->getAllowedRegulationDocumentTypes();

            $result = $helper->saveRegulationDocument($_FILES["regulation_document"], $folder, $allowedRegulationDocumentTypes, false);
            if ($result["status"] == 1) {
                $url = $helper->getVendorUploadedDocumentUrl((int)$vendorId, $result['content']['new_name'], $key);
                $result = array(
                    "status" => 1,
                    "content" => array(
                        'name' => $result['content']['name'],
                        'new_name' => $result['content']['new_name'],
                        'link' => $url
                    )
                );
            }
            if ($result["status"] == 0) {
                $result = array(
                    "status" => 0,
                    "content" => $result["message"]
                );
            }
        }
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }


    public function regulationacceptedAction(){
        return $this->_renderPage();
    }

    public function regulationexpiredAction()
    {
        return $this->_renderPage();
    }

}
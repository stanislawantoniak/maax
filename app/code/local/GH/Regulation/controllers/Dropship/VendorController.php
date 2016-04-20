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
        // Hard code for polish lang - for now we do not have translated regulation
        if ($return = $this->forceSetLocalePolish()) {
            return $return;
        }

        $helper = $this->getHelperData();
        try {
            $id = $this->getRequest()->getParam('id', false);   // Vendor id
            $key = $this->getRequest()->getParam('key', false); // Token

            if (empty($id) || empty($key)) {
                throw new Exception('Bad request.');
            }
            try {

                /* @var $vendor Zolago_Dropship_Model_Vendor */
                $vendor = Mage::getModel('udropship/vendor')->load($id);


                if ((!$vendor) || (!$vendor->getId())) {
                    throw new Exception('Failed to load vendor by id.');
                }
                if ($vendor->getConfirmation() !== $key) {
                    throw new Exception('Wrong confirmation key.');
                }

                $docs = Mage::helper("ghregulation")->getDocumentsToAccept($vendor);
                if (empty($docs)) {
                    $this->_getSession()->addError(Mage::helper("zolagodropshipmicrosite")->__('Wrong confirmation key.'));
                    return $this->_redirect('udropship/vendor/');
                }
                //Check if token not expired

                if ($vendor->getConfirmation() && $vendor->getConfirmationSent()) {
                    /* Vendor account confirmation token life time */
                    $confirmationTokenExpirationTime = Mage::getStoreConfig('udropship/microsite/confirmation_token_expiration_time');

                    $localeTime = Mage::getModel('core/date')->timestamp(time());
                    $secPastSinceConfirmation = $localeTime - strtotime($vendor->getRegulationConfirmRequestSentDate());
                    $hoursPastSinceConfirmation = $secPastSinceConfirmation / 60 / 60;

                    if ($hoursPastSinceConfirmation > $confirmationTokenExpirationTime) {
                        //If token expired redirect to cms page with info "Contact with GALLERY to get new confirmation email"

                        $this->_redirect('udropship/vendor/regulationexpired');
                        return;
                    }
                }
            } catch (Exception $e) {
                throw new Exception($this->__('Wrong confirmation key.'));
            }
        } catch (Exception $e) {
            // die unhappy
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('udropship/vendor/');
            return;
        }
        $this->_renderPage(null, null, $helper->__("Accept regulation"));
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
        $_helper = $this->getHelperData();

        try {
            /* @var $vendor Zolago_Dropship_Model_Vendor */
            $vendor = Mage::getModel('udropship/vendor')->load($vendorId);

            // Input validation START
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            $formKeyPost = $this->getRequest()->getParam('form_key');
            // Check form key
            if ($formKey != $formKeyPost)
                throw Mage::exception('GH_Common');
            // Check vendor
            if (!$vendorId && !$vendor->getId())
                throw Mage::exception('GH_Common', "Undefined vendor");
            // Check vendor
            if ($vendor->getConfirmation() !== $key)
                throw Mage::exception('GH_Common', "Wrong confirmation key.");
            // Check if set acceptation
            if (!$acceptRegulations)
                throw Mage::exception('GH_Common', "Please check Accept Regulation checkbox");
            // Check role
            if (!in_array($acceptRegulationsRole, array(
                GH_Regulation_Helper_Data::REGULATION_DOCUMENT_VENDOR_ROLE_SINGLE,
                GH_Regulation_Helper_Data::REGULATION_DOCUMENT_VENDOR_ROLE_PROXY))
            )   throw Mage::exception('GH_Common', "Please select acceptation type");
            // Input validation END


            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $localeTimeF = date("Y-m-d H:i:s", $localeTime);

            // Set date when vendor accept regulations
            $vendor->setData("regulation_accept_document_date", $localeTimeF);
            $vendor->setData("regulation_accepted", 1);
            $vendor->setConfirmation(null);

            $currentData = $vendor->getDecodedRegulationAcceptDocumentData();
            $currentData["IP"] = $_SERVER['REMOTE_ADDR'];
            $currentData["accept_regulations_role"] = $acceptRegulationsRole;
            $currentData["accept_regulations"] = 1;
            $currentData["accept_regulations_vendor_login"] = $vendor->getEmail();
            $currentData["accept_regulations_declared_by_first_name"] = $vendor->getExecutiveFirstname();
            $currentData["accept_regulations_declared_by_last_name"] = $vendor->getExecutiveLastname();

            if ($acceptRegulationsRole == GH_Regulation_Helper_Data::REGULATION_DOCUMENT_VENDOR_ROLE_SINGLE) {
                // If he upload something and change his mind
                if ($vendor->getRegulationAcceptDocumentPath()) {
                    $folder = GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER . DS . "accept_" . (int)$vendorId;
                    $dirname = Mage::getBaseDir('media') . DS . $folder . DS;
                    $this->deleteDirectory($dirname); // Ordnung muss sein
                    $currentData["document_path"] = '';
                    $currentData["document_name"] = '';
                }
            } else { // GH_Regulation_Helper_Data::REGULATION_DOCUMENT_VENDOR_ROLE_PROXY
                // Checking if file exist
                if (!file_exists($vendor->getRegulationAcceptDocumentFullPath())) {
                    throw Mage::exception('GH_Common', 'You need to upload your document first');
                }
            }
            $vendor->setData("regulation_accept_document_data", json_encode($currentData));

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

            // File name of document witch he just upload (if uploaded )
            $vendorFileName = $vendor->getRegulationAcceptDocumentName();
            // All Attachments for email
            $acceptAttachments = array();
            // Uploaded document by vendor
            if ($vendorFileName && !empty($vendorFileName)) {
                $acceptAttachments[] = array(
                    'filename' => $vendorFileName,
                    'content' => file_get_contents($vendor->getRegulationAcceptDocumentFullPath()),
                    'type' => $_helper->getMimeTypeFromFileName($vendorFileName),
                );
            }
            //Our documents
            /** @var GH_Regulation_Model_Resource_Regulation_Document $docModel */
            $docModel = Mage::getResourceModel("ghregulation/regulation_document");
            $docs = $docModel->getDocumentsToAccept($vendor, true);

            if (count($docs) > 0) {
                /** @var GH_Regulation_Model_Regulation_Document $doc */
                foreach ($docs as $doc) {
                    $acceptAttachments[] = array(
                        'filename'  => $doc->getFileName(),
                        'content'   => file_get_contents($doc->getFullPath()),
                        'type'      => $_helper->getMimeTypeFromFileName($doc->getFileName()),
                    );
                }
            }
            if (!empty($acceptAttachments)) {
                // Set flag for sendVendorRegulationAcceptedEmail for using attachments
                $vendor->setData("accept_attachments", $acceptAttachments);
            }
            // Send accept email
            Mage::helper('umicrosite')->sendVendorRegulationAcceptedEmail($vendor);
            $this->_redirect('udropship/vendor/regulationaccepted');
            /** @see GH_Regulation_Dropship_VendorController::regulationacceptedAction() */
        } catch (GH_Common_Exception $e) {
            if ($e->getMessage())
                $this->_getSession()->addError($_helper->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($_helper->__("An error occurred. Administrator will be noticed about this"));
            return $this->_redirectReferer();
        }
    }

    /**
     * triggers document download based on provided id in request
     *
     * checks if document has rights to this document id and if document with this id exists
     * if everything is ok then download should start, if sth is wrong then returns 404
     *
     * @param null|Zolago_Dropship_Model_Vendor $vendor
     */
    public function getDocumentAction($vendor = null)
    {
        $documentId = $this->getRequest()->getParam('id');
        if ($documentId) {
            /** @var Gh_Regulation_Model_Regulation_Document $document */
            $document = Mage::getModel('ghregulation/regulation_document')->load($documentId);
            $helper = $this->getHelperData();

            /** @var Zolago_Dropship_Model_Session $vendorSession */
            $vendorSession = Mage::getSingleton('udropship/session');

            if (is_null($vendor)) {
                $vendor = $vendorSession->getVendor();
            }

            if ($document->getId() //document exists
                && $vendor->getId() //vendor is logged in
                && in_array($document->getId(),$helper->getVendorDocuments($vendor->getId(),true)) //vendor has rights to provided document
            ) {
                $path = $document->getFullPath();
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
     * Get regulation document (uploaded by admin) for not jet active vendor
     * checks if token is correct and not expired then proceeds to $this->getDocumentAction()
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
                    $secPastSinceConfirmation = $localeTime - strtotime($vendor->getRegulationConfirmRequestSentDate());
                    $hoursPastSinceConfirmation = $secPastSinceConfirmation / 60 / 60;

                    if ($hoursPastSinceConfirmation < $confirmationTokenExpirationTime) {
                        //If token is not expired proceed with document download
                        $this->getDocumentAction($vendor);
                        return;
                    }
                }
            }
        }
        $this->norouteAction();
    }

    /**
     * Return file uploaded by vendor on regulation acceptation steep
     * for udropship front
     */
    public function getVendorUploadedDocumentAction() {
        $req = $this->getRequest();
        $fileName = $req->getParam('file', false);
        $vendorId = $req->getParam('vendor', false);
        $key      = $req->getParam('key', false); // Token

        // If no vendor ID maybe vendor is logged in
        /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
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
            $secPastSinceConfirmation = $localeTime - strtotime($vendor->getRegulationConfirmRequestSentDate());
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
     * rules history
     *
     */
     public function rulesAction() {
         parent::_renderPage(null,'ghregulation');         
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
     * Save vendor document by AJAX
     */
    public function saveVendorDocumentPostAction()
    {
        $helper = $this->getHelperData();
        $req = $this->getRequest();
        $vendorId = $req->getParam('vendor', false);
        $key = $req->getParam('key', false);
        $errorFlag = false;

        /* @var $vendor Zolago_Dropship_Model_Vendor */
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

            $saveData = $helper->saveRegulationDocument($_FILES["regulation_document"], $folder, $allowedRegulationDocumentTypes, false);
            if ($saveData["status"] == 1) {
                $url = $helper->getVendorUploadedDocumentUrl((int)$vendorId, $saveData['content']['new_name'], $key);
                $result = array(
                    "status"  => 1,
                    "content" => array(
                        'name'      => $saveData['content']['name'],
                        'new_name'  => $saveData['content']['new_name'],
                        'link'      => $url
                    )
                );

                // Save data about uploaded file
                $ghRegulationAcceptDocumentData = array(
                    "IP"                      => $_SERVER['REMOTE_ADDR'],
                    "document_path"           => $saveData['content']['path'],
                    "document_name"           => $saveData['content']['new_name'],
                    "accept_regulations_role" => 'proxy',
                    "accept_regulations"      => 0
                );
                $vendor->setData("regulation_accept_document_data", json_encode($ghRegulationAcceptDocumentData));
                Mage::getResourceSingleton('udropship/helper')
                    ->updateModelFields($vendor, array("regulation_accept_document_data"));

            } else {
                $result = array(
                    "status"  => 0,
                    "content" => $saveData["message"]
                );
            }
        }
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function regulationacceptedAction() {
        // Hard code for polish lang - for now we do not have translated regulation
        if ($return = $this->forceSetLocalePolish()) {
            return $return;
        }
        $helper = $this->getHelperData();
        $this->_renderPage(null, null, $helper->__("Thank you for accepting regulations"));
        /** @see app/design/frontend/base/default/template/ghregulation/dropship/regulation/accepted.phtml */
    }

    public function regulationexpiredAction() {
        // Hard code for polish lang - for now we do not have translated regulation
        if ($return = $this->forceSetLocalePolish()) {
            return $return;
        }
        $helper = $this->getHelperData();
        $this->_renderPage(null, null, $helper->__("Regulations expired"));
        /** @see app/design/frontend/base/default/template/ghregulation/dropship/regulation/expired.phtml */
    }

    /**
     * @return GH_Regulation_Helper_Data
     */
    public function getHelperData() {
        return Mage::helper("ghregulation");
    }

    protected function _renderPage($handles=null, $active=null, $title = null)
    {
        $this->_setTheme();
        $this->loadLayout($handles);
        $root = $this->getLayout()->getBlock('root');

        if ($root) {
            $root->addBodyClass('udropship-vendor');
        }
        if ($active && ($header = $this->getLayout()->getBlock('header'))) {
            $header->setActivePage($active);
        }
        if (!empty($title)) {
            $head = $this->getLayout()->getBlock('head');
            $head->setTitle($title);
        }
        /*
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $pager = $this->getLayout()->getBlock('shipment.grid.toolbar');
            if (!$pager) {
                $pager = $this->getLayout()->getBlock('product.grid.toolbar');
            }
            if ($pager) {
                $pager->setTemplate('page/html/pager13.phtml');
            }
        }
        */
        $this->_initLayoutMessages('udropship/session');
        if (is_array($this->_extraMessageStorages) && !empty($this->_extraMessageStorages)) {
            foreach ($this->_extraMessageStorages as $ilm) {
                $this->_initLayoutMessages($ilm);
            }
        }
        $this->renderLayout();
    }

    /**
     * Hard code for polish lang - for now we do not have translated regulation
     * @return false|Mage_Core_Controller_Varien_Action
     */
    public function forceSetLocalePolish() {
        //
        $locale = $this->_getSession()->getLocale();
        if ($locale != 'pl_PL') {
            $this->_getSession()->setLocale('pl_PL');
            $currentUrl = Mage::helper('core/url')->getCurrentUrl();
            return $this->_redirectUrl($currentUrl); // Make refresh page
        }
        return false;
    }
}
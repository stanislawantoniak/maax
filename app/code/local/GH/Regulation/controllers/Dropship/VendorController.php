<?php

/**
 * /**
 * Class GH_Regulation_VendorController
 */
class GH_Regulation_Dropship_VendorController
    extends Zolago_Dropship_Controller_Vendor_Abstract
{
    /**
     *
     */
    public function acceptAction()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            $key = $this->getRequest()->getParam('key', false);

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


                // activate customer
//                try {
//                    $vendor->setConfirmation(null);
//                    $password = Mage::helper('udmspro')->processRandomPattern('[AN*6]');
//                    $vendor->setPassword($password);
//                    $vendor->setPasswordEnc(Mage::helper('core')->encrypt($password));
//                    $vendor->setPasswordHash(Mage::helper('core')->getHash($password, 2));
//                    Mage::getResourceSingleton('udropship/helper')->updateModelFields($vendor, array('confirmation', 'password_hash', 'password_enc'));
//                } catch (Exception $e) {
//                    throw new Exception($this->__('Failed to confirm vendor account.'));
//                }
//
//                Mage::helper('umicrosite')->sendVendorWelcomeEmail($vendor);
//                $this->_getSession()->addSuccess("You've successfully confirmed your account. Please check your mailbox for email with your account information in order to login.");
//                $this->_redirect('udropship/vendor/');
//                return;
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

    public function acceptPostAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        Mage::log($_POST);
        $_helper = Mage::helper("ghregulation");
        if (empty($_POST)) {
            $this->_getSession()->addError($_helper->__("Some error occurred"));
            return $this->_redirectReferer();
        }
        // Form key valid?
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $formKeyPost = $this->getRequest()->getParam('form_key');
        if ($formKey != $formKeyPost) {
            return $this->_redirectReferer();
        }


        $vendorId = $this->getRequest()->getPost('vendor', false);
        $acceptRegulations = $this->getRequest()->getPost('accept_regulations', false);

        $accept_regulations_single = $this->getRequest()->getPost('accept_regulations_single', false);
        $accept_regulations_proxy = $this->getRequest()->getPost('accept_regulations_proxy', false);
        Mage::log($vendorId);
        if (!$vendorId) {
            $this->_getSession()->addError($_helper->__("Undefined vendor"));
            return $this->_redirectReferer();
        }

        if (!$acceptRegulations) {
            $this->_getSession()->addError($_helper->__("Please check Accept Regulation checkbox"));
            return $this->_redirectReferer();
        }
        if (!$accept_regulations_single && !$accept_regulations_proxy) {
            $this->_getSession()->addError($_helper->__("One of the options should be checked"));
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

            if (round($size / 1048576, 1) >= GH_Regulation_Helper_Data::REGULATION_DOCUMENT_MAX_SIZE) { //5MB
                $this->_getSession()->addError($_helper->__("File too large. File must be less than %sMB.", GH_Regulation_Helper_Data::REGULATION_DOCUMENT_MAX_SIZE));
                return $this->_redirectReferer();
            }

        }

        // activate customer
        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);

        try {
            $vendor->setConfirmation(null);
            $password = Mage::helper('udmspro')->processRandomPattern('[AN*6]');
            $vendor->setPassword($password);
            $vendor->setPasswordEnc(Mage::helper('core')->encrypt($password));
            $vendor->setPasswordHash(Mage::helper('core')->getHash($password, 2));

            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $localeTimeF = date("Y-m-d H:i:s", $localeTime);

            $vendor->setData("regulation_accept_document_date", $localeTimeF);

            $gh_regulation_accept_document_data = array(
                "IP" => $_SERVER['REMOTE_ADDR'],
                "document" => $_POST["regulation_document_path"],
                "accept_regulations_single" => isset($_POST['accept_regulations_single']) ? 1 : 0,
                "accept_regulations_proxy" => isset($_POST['accept_regulations_proxy']) ? 1 : 0,
                "accept_regulations" => isset($_POST['accept_regulations']) ? 1 : 0
            );

            $vendor->setData("regulation_accept_document_data", json_encode($gh_regulation_accept_document_data));
            Mage::log($vendor->getData(), null, "save.log");
            Mage::getResourceSingleton('udropship/helper')
                ->updateModelFields(
                    $vendor,
                    array(
                        'confirmation',
                        'password_hash',
                        'password_enc',
                        "regulation_accept_document_date",
                        "regulation_accept_document_data"
                    )
                );

        } catch (Exception $e) {
            throw new Exception($this->__('Failed to confirm vendor account.'));
        }
        $accept_attachments = array();
        //uploaded document by vendor
        if(isset($_POST["regulation_document_path"]) && !empty($_POST["regulation_document_path"])){
            $accept_attachments[] = array(
                'filename' => $name,
                'content' => file_get_contents(Mage::getBaseDir("media"). DS. $_POST["regulation_document_path"]),
                'type' => $type,
            );
        }
        //our documents
        $docs = Mage::getModel("ghregulation/regulation_document")->getAcceptDocumentsList();
        if ($docs->getSize() > 0) {
            foreach ($docs as $doc) {
                $data = unserialize($doc->getDocumentLink());
                $accept_attachments[] = array(
                    'filename' => $data['file_name'],
                    'content' => file_get_contents(Mage::getBaseDir("media") . DS . GH_Regulation_Helper_Data::REGULATION_DOCUMENT_ADMIN_FOLDER . DS . $data['path']),
                    'type' => $data['type'],
                );
            }
        }
        if (!empty($accept_attachments)) {
            $vendor->setData("accept_attachments", $accept_attachments);
        }

        Mage::helper('umicrosite')->sendVendorWelcomeEmail($vendor);
        $this->_getSession()->addSuccess("You've successfully confirmed your account. Please check your mailbox for email with your account information in order to login.");
        $this->_redirect('udropship/vendor/');
        return;


    }

    public function getDocumentAction()
    {
        $documentId = $this->getRequest()->getParam('id');
        if ($documentId) {
            /** @var Gh_Regulation_Model_Regulation_Document $document */
            $document = Mage::getModel('ghregulation/regulation_document')->load($documentId);
            if ($document->getId()) {
                $path = Mage::getBaseDir('media') . DS . GH_Regulation_Helper_Data::REGULATION_DOCUMENT_ADMIN_FOLDER . DS . $document->getPath();
                if (is_file($path) && is_readable($path)) {
                    $this->_sendFile($path, $document->getFileName());
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
        $result = array(
            "status" => 0,
            "content" => array()
        );
        Mage::log($_FILES["regulation_document"]);
        if (isset($_FILES["regulation_document"]) && !empty($_FILES["regulation_document"])) {
            $folder = GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER . DS . "accept_" . (int)$_POST["vendor"];

            $dirname = Mage::getBaseDir('media') . DS . $folder . DS;
            $this->deleteDirectory($dirname);
            $allowedRegulationDocumentTypes = Mage::helper("ghregulation")->getAllowedRegulationDocumentTypes();

            $name = $_FILES["regulation_document"]["name"];
            $result = Mage::helper("ghregulation")->saveRegulationDocument($_FILES["regulation_document"], $folder, $allowedRegulationDocumentTypes);
            if ($result["status"] == 1) {
                $result = array(
                    "status" => 1,
                    "content" => array(
                        'name' => $result["content"]["name"],
                        'link' => $folder . DS . $result["content"]["path"]
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


    public function regulationexpiredAction()
    {
        return $this->_renderPage();
    }

}
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
        $params = $this->getRequest()->getPost();
        //krumo($params);
        //krumo($_FILES);

        if (isset($_FILES["regulation_document"]) && !empty($_FILES["regulation_document"])) {
            $documents = array();
            $folder = GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER;
            $allowedRegulationDocumentTypes = Mage::helper("ghregulation")->getAllowedRegulationDocumentTypes();
            foreach ($_FILES["regulation_document"] as $documentDataName => $regulationDocumentData) {
                for ($i = 0; $i < count($regulationDocumentData); $i++) {
                    $documents[$i][$documentDataName] = $regulationDocumentData[$i];
                }
            }
            if (!empty($documents)) {
                foreach ($documents as $regulationDocument) {
                    Mage::helper("ghregulation")->saveRegulationDocument($regulationDocument, $folder, $allowedRegulationDocumentTypes);
                }
            }
            //krumo($documents);
        }

        //die("test");
    }

    public function getDocumentAction() {
        $documentId = $this->getRequest()->getParam('id');
        if($documentId) {
            /** @var Gh_Regulation_Model_Regulation_Document $document */
            $document = Mage::getModel('ghregulation/regulation_document')->load($documentId);
            if($document->getId()) {
                $path = Mage::getBaseDir('media') . DS . GH_Regulation_Helper_Data::REGULATION_DOCUMENT_ADMIN_FOLDER . DS .$document->getPath();
                if(is_file($path) && is_readable ($path)) {
                    $this->_sendFile($path,$document->getFileName());
                    return;
                }
            }
        }
        $this->norouteAction(); //404
        return;
    }

    protected function _sendFile($filepath,$filename = null) {
        $filename = is_null($filename) ? basename($filepath) : $filename;

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            //->setHeader ( 'Content-type', 'application/pdf', true ) /*  View in browser */
            ->setHeader('Content-type', 'application/force-download') /*  Download        */
            ->setHeader('Content-Length', filesize($filepath))
            ->setHeader('Content-Disposition', 'inline' . '; filename=' . $filename);
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($filepath);
    }

    /**
     * Save vendor document bt AJAX
     */
    public function saveVendorDocumentPostAction()
    {
        if (isset($_FILES["regulation_document"]) && !empty($_FILES["regulation_document"])) {
            $folder = GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER;
            $allowedRegulationDocumentTypes = Mage::helper("ghregulation")->getAllowedRegulationDocumentTypes();

            $name = $_FILES["regulation_document"]["name"];
            $path = Mage::helper("ghregulation")->saveRegulationDocument($_FILES["regulation_document"], $folder, $allowedRegulationDocumentTypes);
            //echo $name;
            //echo $path;
        }
    }


    public function regulationexpiredAction()
    {
        return $this->_renderPage();
    }

}
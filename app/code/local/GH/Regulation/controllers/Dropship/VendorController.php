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
            $id      = $this->getRequest()->getParam('id', false);
            $key     = $this->getRequest()->getParam('key', false);

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
                die("test");
                // activate customer
                try {
                    $vendor->setConfirmation(null);
                    $password = Mage::helper('udmspro')->processRandomPattern('[AN*6]');
                    $vendor->setPassword($password);
                    $vendor->setPasswordEnc(Mage::helper('core')->encrypt($password));
                    $vendor->setPasswordHash(Mage::helper('core')->getHash($password, 2));
                    Mage::getResourceSingleton('udropship/helper')->updateModelFields($vendor, array('confirmation','password_hash','password_enc'));
                }
                catch (Exception $e) {
                    throw new Exception($this->__('Failed to confirm vendor account.'));
                }

                Mage::helper('umicrosite')->sendVendorWelcomeEmail($vendor);
                $this->_getSession()->addSuccess("You've successfully confirmed your account. Please check your mailbox for email with your account information in order to login.");
                $this->_redirect('udropship/vendor/');
                return;
            }
            catch (Exception $e) {
                throw new Exception($this->__('Wrong vendor account specified.'));
            }
        }
        catch (Exception $e) {
            // die unhappy
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('udropship/vendor/');
            return;
        }
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
                    $this->_saveRegulationDocument($regulationDocument, $folder, $allowedRegulationDocumentTypes);
                }
            }
            //krumo($documents);
        }

        //die("test");
    }


    /**
     * @param $file
     * @param $folder
     * @param $allowedRegulationDocumentTypes
     * @return string
     */
    protected function _saveRegulationDocument($file, $folder, $allowedRegulationDocumentTypes)
    {
        $path = "";

        $tmpName = $file["tmp_name"];
        $name = $file["name"];
        $type = $file["type"];
        $size = $file["size"];


        if (!in_array($type, $allowedRegulationDocumentTypes)) {
            return $path;
        }

        if (!empty($name)) {
            $imageName = $this->cleanFileName($name);
            $uniqName = uniqid() . "_" . $imageName;

            $image = md5_file($tmpName);
            $image = md5(mt_rand() . $image);
            $safeFolderPath = $image[0] . "/" . $image[1] . "/";

            mkdir(Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath, 0777, true);
            $path = $safeFolderPath . $uniqName;
            try {
                move_uploaded_file($tmpName, Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath . $uniqName);
            } catch (Exception $e) {
                Mage::logException($e);
            }

        }
        return $path;
    }

    /**
     * Clean file name
     * @param $string
     * @return mixed
     */
    function cleanFileName($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^.A-Za-z0-9\-]/', '', $string); // Removes special chars.
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
}
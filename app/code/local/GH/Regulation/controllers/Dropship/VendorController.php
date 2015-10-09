<?php

/**
 * /**
 * Class GH_Regulation_VendorController
 */
class GH_Regulation_Dropship_VendorController
    extends Zolago_Dropship_Controller_Vendor_Abstract
{
    public function acceptAction()
    {
        $this->_renderPage();
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
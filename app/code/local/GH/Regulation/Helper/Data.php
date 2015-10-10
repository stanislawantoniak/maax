<?php

/**
 * Class GH_Regulation_Helper_Data
 */
class GH_Regulation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REGULATION_DOCUMENT_FOLDER = "vendor_regulation";
    const REGULATION_DOCUMENT_ADMIN_FOLDER = "admin_regulation";

    const REGULATION_DOCUMENT_MAX_SIZE = 5; //MB

    /**
     * @return array
     */
    public static function getAllowedRegulationDocumentTypes()
    {
        return array("image/png", "image/jpg", "image/jpeg", "application/pdf");
    }

    /**
     * @param $file
     * @param $folder
     * @param $allowedRegulationDocumentTypes
     * @return array
     */
    public function saveRegulationDocument($file, $folder, $allowedRegulationDocumentTypes)
    {
        $_helper = Mage::helper("ghregulation");
        $result = array("status" => 0, "message" => "", "content" => array());

        $tmpName = $file["tmp_name"];
        $name = $file["name"];
        $type = $file["type"];
        $size = $file["size"];

        if (!in_array($type, $allowedRegulationDocumentTypes)) {
            $result = array("status" => 0, "message" => $_helper->__("File must be JPG, PNG or PDF"));
            return $result;
        }
        if (round($size / 1048576, 1) >= self::REGULATION_DOCUMENT_MAX_SIZE) { //5MB
            $result = array("status" => 0, "message" => $_helper->__("File too large. File must be less than %sMB.", GH_Regulation_Helper_Data::REGULATION_DOCUMENT_MAX_SIZE));
            return $result;
        }

        if (!empty($name)) {
            $imageName = $this->cleanFileName($name);
            $uniqName = uniqid() . "_" . $imageName;

            $image = md5_file($tmpName);
            $image = md5(mt_rand() . $image);
            $safeFolderPath = $image[0] . "/" . $image[1] . "/";

            @mkdir(Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath, 0777, true);

            $path = $safeFolderPath . $uniqName;
            $result = array("status" => 1, "content" => array("path" => $path, "name" => $name));
            try {
                move_uploaded_file($tmpName, Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath . $uniqName);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $result;
    }

    /**
     * Clean file name
     * @param $string
     * @return mixed
     */
    public static function cleanFileName($string)
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^.A-Za-z0-9\-\_]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return preg_replace('/_+/', '_', $string); // Replaces multiple underscores with single one.
    }
}
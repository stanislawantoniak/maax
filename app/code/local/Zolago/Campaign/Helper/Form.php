<?php

/**
 * Class Zolago_Campaign_Helper_Form
 */
class Zolago_Campaign_Helper_Form extends Mage_Core_Helper_Abstract
{

    /**
     * @param $imageName
     * @param $imageTmpName
     * @param $imageFolder
     * @return string
     */
    public function saveFormImage($imageName, $imageTmpName, $imageFolder)
    {
        $imageName = GH_Common_Helper_Data::cleanFileName($imageName);
        $uniqName = uniqid() . "_" . $imageName;

        $image = md5_file($imageTmpName);
        $image = md5(mt_rand() . $image);
        $safeFolderPath = $image[0] . "/" . $image[1] . "/" . $image[2] . "/";

        mkdir(Mage::getBaseDir('media') . DS . $imageFolder . DS . $safeFolderPath, 0777, true);
        $path = $safeFolderPath . $uniqName;
        try {
            move_uploaded_file($imageTmpName, Mage::getBaseDir('media') . DS . $imageFolder . DS . $safeFolderPath . $uniqName);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $path;
    }

}
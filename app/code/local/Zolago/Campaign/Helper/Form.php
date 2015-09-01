<?php

/**
 * Class Zolago_Campaign_Helper_Form
 */
class Zolago_Campaign_Helper_Form extends Mage_Core_Helper_Abstract
{

    public function saveFormImage($imageName, $imageTmpName, $imageFolder)
    {
        $uniqName = uniqid() . "_" . $imageName;

        $image = md5_file($imageTmpName);
        $image = md5(mt_rand() . $image);
        $safeFolderPath = $image[0] . "/" . $image[1] . "/" . $image[2] . "/";
        mkdir($imageFolder . DS . $safeFolderPath, 0777, true);
        $path = $imageFolder . DS . $safeFolderPath . $uniqName;
        try {
            move_uploaded_file($imageTmpName, $path);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $path;
    }

}
<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Helper_Provider_Images extends Mage_Core_Helper_Abstract
{
    const UPLOAD_VENDOR_CONST = "magpleasure";
    const UPLOAD_SUBDIR_CONST = "upload";

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getExtensionByType($mimeType)
    {
        list($type, $extension) = explode("/", $mimeType);

        $extension = str_replace(array(
            "jpg",
            "jpeg",
            "pjpeg",
        ), "jpg", $extension);

        return $extension;
    }

    public function getNewName($mimeType)
    {
        return md5(date('YmdHis') . rand(0, 255)) . "." . $this->_getExtensionByType($mimeType);
    }

    protected function _getStorageParts()
    {
        return array(
            self::UPLOAD_VENDOR_CONST,
            "mpblog",
            self::UPLOAD_SUBDIR_CONST,
        );
    }

    protected function _getNameBasedParts($fileName)
    {
        return array(
            $fileName[0],
            $fileName[1],
        );
    }

    protected function _getDestinationParts($fileName)
    {
        return array_merge($this->_getStorageParts(), $this->_getNameBasedParts($fileName));
    }

    public function getDestinationUrl($fileName)
    {
        $url =
            Mage::getBaseUrl('media') .
            implode("/", $this->_getDestinationParts($fileName)) . "/" . $fileName;

        return $url;
    }

    public function getDestinationPath($fileName)
    {
        $path =
            Mage::getBaseDir('media') . DS .
            implode(DS, $this->_getDestinationParts($fileName));

        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        return $path;
    }

    public function getFilePath($fileName)
    {
        return $this->getDestinationPath($fileName) . DS . $fileName;
    }

    protected function _RecursiveSearch($folder, $pattern)
    {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = array();
        foreach ($ite as $pathname => $fileinfo) {
            if (preg_match($pattern, $pathname)){
                $files[] = $pathname;
            }
        }

        return $files;
    }

    protected function _compareImages($a, $b)
    {
        if ($a['mtime'] == $b['mtime']) {
            return 0;
        }
        return ($a['mtime'] > $b['mtime']) ? -1 : 1;
    }
    public function getImageList()
    {
        $path = Mage::getBaseDir('media') . DS . implode(DS, $this->_getStorageParts());
        $pattern = "/^((?!cache).)*\\.(jpg|png|gif)$/i";

        $images = $this->_RecursiveSearch($path, $pattern);
        $images = array_unique($images);
        $result = array();

        $date = new Zend_Date();
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        $timezodeOffset = $this->_helper()->getTimeZoneOffset();
        $fileHelper = $this->_helper()->getCommon()->getFiles();
        $image = $this->_helper()->getCommon()->getImage();
        $destPath = null;

        foreach ($images as $imagePath) {

            $date->setTimestamp(filemtime($imagePath))->addSecond($timezodeOffset);

            $destPath = str_replace(Mage::getBaseDir('media') . DS, "", $imagePath);
            $image->init($destPath)->adaptiveResize(100, 75);

            $result[] = array(
                'thumb' => $image->__toString(),
                'image' => $this->getDestinationUrl($fileHelper->getBaseName($imagePath)),
                'title' => $date->toString($format, null, $localeCode),
                'mtime' => $date->getTimestamp()
            );
        }

        uksort($result, array($this, '_compareImages'));
        return $result;
    }
}
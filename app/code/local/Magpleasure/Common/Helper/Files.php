<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Files extends Mage_Core_Helper_Abstract
{
    protected $_allowedFiles = array('jpeg', 'jpg', 'png', 'gif',
        'bmp', 'psd', 'psp', 'ai', 'eps', 'cdr',
        'mp3', 'mp4', 'wav', 'aac', 'aiff', 'midi',
        'avi', 'mov', 'mpg', 'flv', 'mpa',
        'pdf', 'txt', 'rtf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'djvu', 'djv',
        'bat', 'cmd', 'dll', 'inf', 'ini', 'ocx', 'sys',
        'htm', 'html', 'write', 'none',
        'zip', 'rar', 'dmg');

    protected $_allowedImages = array(
        'jpeg', 'jpg', 'png', 'gif', 'bmp',
    );

    /**
     * Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    protected function _getPathInfo($fileName, $key)
    {
        $pathParts = pathinfo($fileName);
        return isset($pathParts[$key]) ? $pathParts[$key] : false;
    }

    public function getBaseName($fileName)
    {
        return $this->_getPathInfo($fileName, 'basename');
    }

    public function getFileName($fileName)
    {
        return $this->_getPathInfo($fileName, 'filename');
    }

    public function getDirName($fileName)
    {
        return $this->_getPathInfo($fileName, 'dirname');
    }

    public function getExtension($fileName)
    {
        return $this->_getPathInfo($fileName, 'extension');
    }

    /**
     * Save Content to
     *
     * @param $fileName
     * @param $content
     * @param bool|true $overwrite
     * @param null $fileObject
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function saveContentToFile($fileName, $content, $overwrite = true, &$fileObject = null)
    {
        if ($fileName){

            if (!file_exists($fileName)){

                $dirName = $this->getDirName($fileName);
                if (!file_exists($dirName)){
                    mkdir($dirName, 0755, true);
                }

                file_put_contents($fileName, $content);

            } else {

                if ($overwrite){

                    file_put_contents($fileName, $content);
                } else {
                    Mage::throwException(sprintf("File is already exists. [%s]", $fileName));
                }
            }
        }

        return $this;
    }

    public function getContentFromFile($fileName)
    {
        return file_get_contents($fileName);
    }

    public function getAllowedImageExtensions()
    {
        return $this->_allowedImages;
    }

    public function getAllowedFileExtensions()
    {
        return $this->_allowedFiles;
    }
}
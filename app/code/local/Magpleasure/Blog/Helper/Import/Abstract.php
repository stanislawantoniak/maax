<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

abstract class Magpleasure_Blog_Helper_Import_Abstract
    extends Magpleasure_Blog_Helper_Data
{
    protected $_data;
    protected $_verbose = false;
    
    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return boolean
     */
    public function isVerbose()
    {
        return $this->_verbose;
    }

    /**
     * @param $verbose
     * @return $this
     */
    public function setVerbose($verbose)
    {
        $this->_verbose = $verbose;
        return $this;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Echo into console
     *
     * @return $this
     */
    protected function _printLn()
    {
        if ($this->isVerbose()){

            echo call_user_func_array(
                    "sprintf",
                    func_get_args()
                ) . "\n";
        }

        return $this;
    }
    
    
    /**
     * Prepare data by mask
     * @param array $mask
     * @param array $source
     * @return array
     */
    protected function _prepareDataFromArray(array $mask, array $source)
    {
        $data = array();
        foreach ($mask as $sourceKey => $targetKey){

            if (isset($source[$sourceKey])){

                if (is_array($source[$sourceKey]) && !count($source[$sourceKey])){
                    $source[$sourceKey] = null;
                }

                if (is_array($targetKey)){
                    foreach ($targetKey as $targetSubKey){
                        $data[$targetSubKey] = $source[$sourceKey];
                    }
                } else {
                    $data[$targetKey] = $source[$sourceKey];
                }
            }
        }
        return $data;
    }

    /**
     * Prepare data by mask
     * @param array $mask
     * @param Mage_Core_Model_Abstract $source
     * @return array
     */
    protected function _prepareData(array $mask, Mage_Core_Model_Abstract $source)
    {
        $data = array();
        foreach ($mask as $sourceKey => $targetKey){
            if (is_array($targetKey)){
                foreach ($targetKey as $targetSubKey){
                    $data[$targetSubKey] = $source->getData($sourceKey);
                }
            } else {
                $data[$targetKey] = $source->getData($sourceKey);
            }
        }
        return $data;
    }

    protected function _processor(array $map, Mage_Core_Model_Abstract $source)
    {
        foreach ($map as $key => $method){

            $source->setData(
                $key,
                call_user_func_array(
                    array($this, $method), # Method name
                    array(
                        $source->getData($key), # Content
                        $source                 # Processing Model
                    )
                )
            );
        }

        return $this;
    }

    public function import($verbose = false, $data = array())
    {
        $this->setVerbose($verbose);
        $this->_data = $data;
        return $this;
    }

    /**
     * @param $remoteSrc
     * @return bool
     */
    protected function _localizeSrc($remoteSrc)
    {
        $filesHelper = $this
            ->_helper()
            ->getCommon()
            ->getFiles();

        $this->_printLn("--> Try to download image - %s", $remoteSrc);
        try {
            $content = file_get_contents($remoteSrc);

            if ($content) {

                preg_match("/[^\/\&\?]+\.\w{3,4}(?=([\?&].*$|$))/i", $remoteSrc, $matches);

                if (isset($matches[0])) {
                    $fileName = $matches[0];

                    $newFilePath =
                        "/wysiwyg/wordpress/" .
                        strtolower($fileName[0]) .
                        "/" .
                        strtolower($fileName[1]) .
                        "/" .
                        $fileName;

                    $newFullPath = Mage::getBaseDir('media') . str_replace("/", DS, $newFilePath);


                    # Should correct file path
                    # if it already exists
                    if (file_exists($newFullPath)){

                        $baseFileName = $filesHelper->getFileName($newFullPath);
                        $extFileName = $filesHelper->getExtension($newFullPath);

                        $fileName = implode("", array(
                            $baseFileName,
                            "-",
                            str_split(md5(time()), 3)[0],
                            ".",
                            $extFileName
                        ));

                        $newFilePath =
                            "/wysiwyg/wordpress/" .
                            strtolower($fileName[0]) .
                            "/" .
                            strtolower($fileName[1]) .
                            "/" .
                            $fileName;

                        $newFullPath = Mage::getBaseDir('media') . str_replace("/", DS, $newFilePath);
                    }


                    $filesHelper->saveContentToFile($newFullPath, $content);
                    $localSrc = Mage::getBaseUrl('media') . $newFilePath;
                    $this->_printLn("    OK. Saved to - %s", $localSrc);

                    return $localSrc;
                }
            } else {
                Mage::throwException("Unable to download.");
            }

        } catch (Exception $e) {
            $this->_printLn("    Failed: %s", $e->getMessage());
        }

        return false;;
    }

    protected function _xmlToArray($xml, $options = array())
    {
        $xml = simplexml_load_string($xml);
        return $this->_simpleXmlToArray($xml);
    }

    /**
     * Thanks to http://outlandish.com/blog/xml-to-json/
     * We love this code ヽ(^ᴗ^)丿
     *
     * @param $xml string
     * @param array $options
     * @return array
     */
    protected function _simpleXmlToArray($xml, $options = array())
    {
        $defaults = array(
            'namespaceSeparator' => ':',# you may want this to be something other than a colon
            'attributePrefix' => '@',   # to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(),   # array of xml tag names which should always become arrays
            'autoArray' => true,        # only create arrays for tags which appear more than once
            'textContent' => '$',       # key used for the text content of elements
            'autoText' => true,         # skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       # optional search and replace on tag and attribute names
            'keyReplace' => false       # replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);

        if ($xml){

        }

        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; # add base (empty) namespace

        # get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                # replace characters in attribute name
                if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        # get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                # recurse into child nodes
                $childArray = $this->_simpleXmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                # replace characters in tag name
                if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                # add namespace prefix, if any
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    # only entry with this key
                    # test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    # key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    # key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        # get text content of node
        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        # stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        # return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }
}
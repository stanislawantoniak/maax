<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Xml extends Mage_Core_Helper_Abstract
{

    protected function _arrayToXml($data, &$xml)
    {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $this->_arrayToXml($value, $xml->addChild("$key"));
                } else{
                    $this->_arrayToXml($value, $xml);
                }
            } else {
                $xml->addChild("$key","$value");
            }
        }
    }

    /**
     * Convert parametric array to XML
     *
     * @param array $data
     * @return SimpleXMLElement
     */
    public function getParamArrayToXml(array $data)
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><config></config>");
        $this->_arrayToXml($data, $xml);
        return $xml;
    }
}
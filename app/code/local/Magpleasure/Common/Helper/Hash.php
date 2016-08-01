<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Hash extends Mage_Core_Helper_Abstract
{
    /**
     * Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Get Data from Hash
     *
     * @param string $hash
     * @return array
     */
    public function getData($hash)
    {
        $result = array();
        if ($hash){
            $data = $this->_commonHelper()->urlDecode($hash);
            try {
                $result = unserialize($data);
            } catch (Exception $e){
                $this->_commonHelper()
                    ->getException()
                    ->logException($e)
                    ;
            }
        }
        return $result;
    }

    /**
     * Get Hash from Data
     *
     * @param array $data
     * @return string
     */
    public function getHash(array $data)
    {
        $data = serialize($data);
        return $this->_commonHelper()->getCore()->urlEncode($data);
    }

    /**
     * Get Varien_Object from hash
     *
     * @param $hash
     * @return Varien_Object
     */
    public function getObjectFromHash($hash)
    {
        return new Varien_Object($this->getData($hash));
    }

    protected function _dataToString($data, $key = false)
    {
        if (!is_array($data)){
            return ($key ? $key : "").$data;
        } elseif (is_array($data)){
            $result = "";
            foreach ($data as $key=>$value){
                if (is_array($value)){
                    $result .= $this->_dataToString($value, $key);
                } else {
                    $result .= $key.$value;
                }
            }
            return $result;
        } else {
            return "NULL";
        }
    }

    public function getFastMd5Hash($data)
    {
        return md5($this->_dataToString($data));
    }
}
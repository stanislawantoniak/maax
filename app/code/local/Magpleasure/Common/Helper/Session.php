<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Session extends Mage_Core_Helper_Abstract
{
    const AREA_FRONTEND = 'frontend';
    const AREA_BACKEND = 'adminhtml';

    /**
     * Retrieves session for process
     *
     * @param $area
     * @return Mage_Core_Model_Session
     */
    protected function _getSession($area = null)
    {
        if (!$area){
            $area = Mage::app()->getLayout()->getArea();
        }

        if ($area == self::AREA_FRONTEND){
            $session = Mage::getSingleton('customer/session');
        } else {
            $session = Mage::getSingleton('adminhtml/session');
        }
        return $session;
    }

    public function addError($message)
    {
        $this->_getSession()->addError($message);
    }

    public function addSuccess($message)
    {
        $this->_getSession()->addSuccess($message);
    }

    public function addToSession($key, $value, $area = self::AREA_FRONTEND)
    {
        $session = $this->_getSession($area);
        $array = $session->getData($key);
        if ( $array && is_array($array) && count($array) ){
            if (!in_array($value, $array)){
                $array[] = $value;
            }
        } else {
            $array = array($value);
        }
        $session->setData($key, $array);
        return $this;
    }

    public function removeFromSession($key, $value, $area = self::AREA_FRONTEND)
    {
        $session = $this->_getSession($area);
        $array = $session->getData($key);

        if ( $array && is_array($array)){
            $index = array_search($value, $array);
            if ($index !== false){
                unset($array[$index]);
            }
        } else {
            $array = array();
        }
        $session->setData($key, $array);
        return $this;
    }

    public function isInSession($key, $value, $area = self::AREA_FRONTEND)
    {
        $session = $this->_getSession($area);
        $array = $session->getData($key);
        if ( $array && is_array($array) ){
            return in_array($value, $array);
        } else {
            return false;
        }
    }

    public function getAllValues($key, $area = self::AREA_FRONTEND)
    {
        $session = $this->_getSession($area);
        $array = $session->getData($key);
        if ( $array && is_array($array) ){
            return $array;
        } else {
            return array();
        }
    }


}
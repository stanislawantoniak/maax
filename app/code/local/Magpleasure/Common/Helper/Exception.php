<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Exception extends Mage_Core_Helper_Abstract
{
    /**
     * Log Exception to Global Log
     *
     * @param Exception $e
     * @return Magpleasure_Common_Helper_Exception
     */
    public function logException(Exception $e)
    {
        Mage::logException($e);
        return $this;
    }

}
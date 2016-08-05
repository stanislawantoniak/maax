<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Field_Default
{
    protected $_key;

    public function getProcessableValue($object)
    {
        if ($object){

            if ($object instanceof Mage_Core_Model_Abstract){
                $value = $object->getData($this->getKey());
            } else {
                $value = $object;
            }

            return $this
                ->_helper()
                ->getTextTransformer()
                ->htmlToWords($value)
            ;
        }

        return false;
    }

    /**
     * Field Key
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Field Key
     *
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }
}
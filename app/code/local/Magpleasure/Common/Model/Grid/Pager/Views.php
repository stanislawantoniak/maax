<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Model_Grid_Pager_Views extends Magpleasure_Common_Model_System_Config_Source_Abstract
{
    protected $_views = array(20, 30, 50, 100, 200);

    protected function _helper()
    {
        return Mage::helper('common');
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->_views as $key => $value) {
            $data[$value] = $value;
        }
        return $data;
    }

    public function toJson()
    {
        $arr = $this->toArray();
        $result = array();
        foreach ($arr as $key => $value) {
            $result[] = array('id' => $value, 'name' => $value);

        }
        return Zend_Json::encode($result);

    }


}
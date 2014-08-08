<?php

class Zolago_Banner_Helper_Data extends Mage_Core_Helper_Abstract {

    public function setBannerTypeUrl(){
        return Mage::getUrl('zolagobanner/vendor/setType');
    }

    public function bannerTypeUrl(){
        return Mage::getUrl('zolagobanner/vendor/type');
    }

    public function bannerEditUrl($type){
        return Mage::getUrl('zolagobanner/vendor/edit', array('type' => $type));
    }


    public function getEditFieldsConfig()
    {
        return array();
    }

    public function getEditFieldsConfigSelect2Json()
    {
        $fConfig = $this->getEditFieldsConfig();

        $fRes = array(array('id'=>'','text'=>$this->__('* Please select')));
        foreach ($fConfig as $efc) {
            if (!is_array($efc['values'])) continue;
            $_fRes = array(
                'text' => $efc['label']
            );
            foreach ($efc['values'] as $fId=>$fLbl) {
                $_fRes['children'][] = array(
                    'id' => $fId,
                    'text' => $fLbl,
                );
            }
            $fRes[] = $_fRes;
        }
        return Mage::helper('core')->jsonEncode($fRes);
    }

    public function serialize($value)
    {
        return Zend_Json::encode($value);
    }
    public function unserialize($value)
    {
        if (empty($value)) {
            $value = empty($value) ? array() : $value;
        } elseif (!is_array($value)) {
            if (strpos($value, 'a:')===0) {
                $value = @unserialize($value);
                if (!is_array($value)) {
                    $value = array();
                }
            } elseif (strpos($value, '{')===0 || strpos($value, '[{')===0) {
                try {
                    $value = Zend_Json::decode($value);
                } catch (Exception $e) {
                    $value = array();
                }
            }
        }
        return $value;
    }
}
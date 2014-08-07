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
}
<?php

class Zolago_Banner_Model_Banner extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init("zolagobanner/banner");
    }

    /**
     * @param array $data
     * @return boolean|array
     */
    public function validate($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = Mage::getSingleton("zolagobanner/banner_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }


    public function saveBannerContent($content){
        $this->getResource()->saveBannerContent($content);
    }

}